(function() {
    var api = jitsi_pro.siteurl + '/wp-json/wp/v2/meeting',
        postsList = [];
    
    const getApiUrl = (url) => {
        let apiUrl = new URL(url),
            params = {per_page: 100};
        apiUrl.search = new URLSearchParams(params).toString();
        return apiUrl;
    };

    const loadPosts = async() => {
        const url = getApiUrl(api);
        const request = await fetch(url);
        const posts = await request.json();
        posts.forEach(post => {
            postsList.push({text: post.title.rendered, value: post.id});
        });
    }

    loadPosts();

    tinymce.PluginManager.add('jitsibutton', function( editor, url ) {
        editor.addButton( 'jitsibutton', {
            tooltip: jitsi_pro.mce_btn_title,
            icon: 'dashicons dashicons-embed-video',
            onclick: function() {
                editor.windowManager.open({
                    title: jitsi_pro.mce_btn_title,
                    body: [
                        {
                            type: 'checkbox',
                            name: 'formPost',
                            label: 'Get from post',
                            value: false
                        },
                        {
                            type: 'listbox',
                            name: 'id',
                            label: 'Select post',
                            value: '',
                            values: postsList
                        },
                        {
                            type: 'textbox',
                            name: 'name',
                            label: 'Name',
                            value: 'Meeting Name'
                        },
                        {
                            type: 'textbox',
                            name: 'width',
                            label: 'Width',
                            value: jitsi_pro.meeting_width
                        },
                        {
                            type: 'textbox',
                            name: 'height',
                            label: 'Height',
                            value: jitsi_pro.meeting_height
                        }
                    ],
                    onsubmit: function( e ) {
                        editor.insertContent(
                            `[${e.data.formPost ? 'jitsi-meet-wp-meeting' : 'jitsi-meet-wp'}
                            ${e.data.id ? ` id="${e.data.id}"` : ""}
                            ${e.data.name ? ` name="${e.data.name}"` : ""} 
                            ${e.data.width ? ` width="${e.data.width}"` : ""}
                            ${e.data.height ? ` height="${e.data.height}"` : ""}/]`);
                    }
                })
            }
        })
    });
})();