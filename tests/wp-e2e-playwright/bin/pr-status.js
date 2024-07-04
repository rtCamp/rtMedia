#!/usr/bin/env node
// Octokit.js
// https://github.com/octokit/core.js#readme

const { Octokit } = require("@octokit/core");

const octokit = new Octokit({
  auth: process.env.TOKEN,
});

octokit.request("POST /repos/{org}/{repo}/statuses/{sha}", {
  org: "rtCamp",
  repo: "qa",
  sha: process.env.SHA ? process.env.SHA : process.env.COMMIT_SHA,
  state: "success",
  conclusion: "success",
  target_url:
    "https://www.tesults.com/results/rsp/view/results/project/772682e7-f0e5-43b9-b96b-cb87ec117270",
  description: "Successfully synced to Tesults",
  context: "E2E Test Result",
});