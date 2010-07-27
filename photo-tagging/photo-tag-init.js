jQuery(document).ready(function(){

                 // Photo tagging js code starts from here
                // code for photo tagging feature
                jQuery('div.photo-container').photoTagger({
                        loadURL:ajaxurl,
                        saveURL:ajaxurl,
                        deleteURL:ajaxurl,
                        isDelayedLoad:true,
                        isTagCreationEnabled:true,
                        isTagDeletionEnabled:true
        });
});