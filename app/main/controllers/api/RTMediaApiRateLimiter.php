<?php
/**
 * Rate limiting for rtMedia API authentication.
 *
 * @package rtMedia
 */

/**
 * Apply per-client and per-identifier limits to API authentication attempts.
 */
class RTMediaApiRateLimiter {

	/**
	 * Object cache group used for rate-limit counters.
	 *
	 * Only relevant when a persistent object cache (Redis, Memcached, etc.)
	 * is active; otherwise counters fall back to transients.
	 *
	 * @var string
	 */
	const CACHE_GROUP = 'rtmedia_api_rate_limit';

	/**
	 * Default rate-limit window in seconds.
	 *
	 * @var int
	 */
	const DEFAULT_WINDOW = 300;

	/**
	 * Default maximum login failures per IP address.
	 *
	 * @var int
	 */
	const DEFAULT_LOGIN_IP_LIMIT = 20;

	/**
	 * Default maximum login failures per identifier.
	 *
	 * @var int
	 */
	const DEFAULT_LOGIN_IDENTIFIER_LIMIT = 5;

	/**
	 * Default maximum invalid token submissions per IP address.
	 *
	 * @var int
	 */
	const DEFAULT_TOKEN_ATTEMPT_LIMIT = 20;

	/**
	 * Check whether a login attempt should be blocked.
	 *
	 * @param string $identifier Username or email address supplied for login.
	 *
	 * @return bool
	 */
	public function is_login_blocked( $identifier ) {
		return $this->get_attempts( $this->get_key( 'login_ip', $this->get_client_ip() ) ) >= $this->get_login_ip_limit()
			|| $this->get_attempts( $this->get_key( 'login_id', $this->normalize_identifier( $identifier ) ) ) >= $this->get_login_identifier_limit();
	}

	/**
	 * Record a failed login attempt.
	 *
	 * @param string $identifier Username or email address supplied for login.
	 */
	public function record_login_failure( $identifier ) {
		$this->increment( $this->get_key( 'login_ip', $this->get_client_ip() ) );
		$this->increment( $this->get_key( 'login_id', $this->normalize_identifier( $identifier ) ) );
	}

	/**
	 * Clear the identifier bucket after a successful login.
	 *
	 * The IP bucket is deliberately retained so an attacker cannot reset it by
	 * successfully authenticating an account they control.
	 *
	 * @param string $identifier Username or email address supplied for login.
	 */
	public function clear_login_identifier( $identifier ) {
		$this->delete( $this->get_key( 'login_id', $this->normalize_identifier( $identifier ) ) );
	}

	/**
	 * Check whether invalid token submissions should be blocked for this client.
	 *
	 * @return bool
	 */
	public function is_token_validation_blocked() {
		return $this->get_attempts( $this->get_key( 'token_ip', $this->get_client_ip() ) ) >= $this->get_token_limit();
	}

	/**
	 * Record an invalid token submission.
	 */
	public function record_token_failure() {
		$this->increment( $this->get_key( 'token_ip', $this->get_client_ip() ) );
	}

	/**
	 * Return the rate-limit window in seconds.
	 *
	 * @return int
	 */
	public function get_window() {
		$window = (int) apply_filters( 'rtmedia_api_rate_limit_window', self::DEFAULT_WINDOW );

		return max( MINUTE_IN_SECONDS, $window );
	}

	/**
	 * Increment a rate-limit bucket and return the new count.
	 *
	 * When a persistent object cache is active (Redis, Memcached, etc.), the
	 * increment is atomic: wp_cache_add() seeds the key exactly once and
	 * wp_cache_incr() is a single atomic operation on the cache backend, so
	 * concurrent requests cannot race on a read-modify-write cycle.
	 *
	 * Without a persistent object cache, this falls back to a transient
	 * (stored in wp_options). That fallback is NOT atomic: two concurrent
	 * requests can both read the same count and both write count+1, silently
	 * losing an increment.
	 *
	 * @param string $key Bucket key.
	 *
	 * @return int
	 */
	private function increment( $key ) {
		$window = $this->get_window();

		if ( wp_using_ext_object_cache() ) {
			// Seed the key only if absent; wp_cache_add() itself is atomic
			// (no-op if the key already exists), so this never clobbers an
			// in-flight counter from a concurrent request.
			wp_cache_add( $key, 0, self::CACHE_GROUP, $window );

			$count = wp_cache_incr( $key, 1, self::CACHE_GROUP );

			// wp_cache_incr() can return false if the key expired between
			// the add() above and the incr() call. Reseed at 1 in that case.
			if ( false === $count ) {
				wp_cache_set( $key, 1, self::CACHE_GROUP, $window );
				$count = 1;
			}

			return (int) $count;
		}

		// Transient fallback — see race-condition note in the docblock above.
		$count = $this->get_attempts( $key ) + 1;
		set_transient( $key, $count, $window );

		return $count;
	}

	/**
	 * Get the number of attempts in a bucket.
	 *
	 * @param string $key Bucket key.
	 *
	 * @return int
	 */
	private function get_attempts( $key ) {
		if ( wp_using_ext_object_cache() ) {
			$count = wp_cache_get( $key, self::CACHE_GROUP );

			return false === $count ? 0 : (int) $count;
		}

		return (int) get_transient( $key );
	}

	/**
	 * Delete a bucket, regardless of backend.
	 *
	 * @param string $key Bucket key.
	 */
	private function delete( $key ) {
		if ( wp_using_ext_object_cache() ) {
			wp_cache_delete( $key, self::CACHE_GROUP );

			return;
		}

		delete_transient( $key );
	}

	/**
	 * Create a bounded, non-reversible bucket key.
	 *
	 * @param string $scope      Rate-limit scope.
	 * @param string $identifier Client or login identifier.
	 *
	 * @return string
	 */
	private function get_key( $scope, $identifier ) {
		return 'rtm_api_rl_' . $scope . '_' . substr( wp_hash( (string) $identifier, 'auth' ), 0, 32 );
	}

	/**
	 * Get the client IP address to key rate limits on.
	 *
	 * By default only REMOTE_ADDR is trusted. Proxy headers such as
	 * X-Forwarded-For or CF-Connecting-IP are never read here because they
	 * can be forged by the client unless a specific, known reverse proxy
	 * strips and rewrites them first — and this plugin has no way to know
	 * whether that's true for any given install.
	 *
	 * @return string
	 */
	private function get_client_ip() {
		$remote_addr = rtm_get_server_var( 'REMOTE_ADDR', 'FILTER_VALIDATE_IP' );
		$remote_addr = $remote_addr ? $remote_addr : 'unknown';

		/**
		 * Filters the client IP address used for API rate limiting.
		 *
		 * @param string $remote_addr The address read from REMOTE_ADDR.
		 */
		$client_ip = apply_filters( 'rtmedia_api_client_ip', $remote_addr );

		$client_ip = is_string( $client_ip ) ? trim( $client_ip ) : '';

		// Validate whatever the filter returned; never trust it blindly,
		// since a badly written filter could hand back attacker-controlled
		// input straight from an unvalidated header.
		return filter_var( $client_ip, FILTER_VALIDATE_IP ) ? $client_ip : 'unknown';
	}

	/**
	 * Normalize a login identifier before deriving its rate-limit key.
	 *
	 * @param string $identifier Username or email address.
	 *
	 * @return string
	 */
	private function normalize_identifier( $identifier ) {
		return strtolower( trim( (string) $identifier ) );
	}

	/**
	 * Get the maximum login failures allowed per IP address.
	 *
	 * @return int
	 */
	private function get_login_ip_limit() {
		return max( 1, (int) apply_filters( 'rtmedia_api_login_ip_limit', self::DEFAULT_LOGIN_IP_LIMIT ) );
	}

	/**
	 * Get the maximum login failures allowed per identifier.
	 *
	 * @return int
	 */
	private function get_login_identifier_limit() {
		return max( 1, (int) apply_filters( 'rtmedia_api_login_identifier_limit', self::DEFAULT_LOGIN_IDENTIFIER_LIMIT ) );
	}

	/**
	 * Get the maximum invalid token submissions allowed per IP address.
	 *
	 * @return int
	 */
	private function get_token_limit() {
		return max( 1, (int) apply_filters( 'rtmedia_api_token_attempt_limit', self::DEFAULT_TOKEN_ATTEMPT_LIMIT ) );
	}
}
