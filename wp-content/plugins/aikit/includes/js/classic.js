"use strict";

(function() {

    tinymce.PluginManager.add('aikit_classic', function(editor, url) {

        let menu = [];

        for (let operation in aikit.prompts) {
            let prompt = aikit.prompts[operation];
            menu.push({
                text: prompt.menuTitle,
                image: aikit.pluginUrl + 'includes/icons/' + prompt.icon + '.svg',
                classes: 'aikit-classic-button',
                onclick: async function () {

                    if (isAiKitProperlyConfigured() === false) {
                        return;
                    }

                    let selectedText = editor.selection.getContent({format: 'text'});
                    if (selectedText === '' && (prompt.requiresTextSelection || prompt.requiresTextSelection === 1 || prompt.requiresTextSelection === '1')) {
                        askUserToSelectText();

                        return;
                    }

                    let selectionRange = editor.selection.getRng();
                    let textBeforeSelection = selectionRange.startContainer.textContent.slice(0, selectionRange.startOffset);
                    let textAfterSelection = selectionRange.endContainer.textContent.slice(selectionRange.endOffset);

                    let dom = tinymce.activeEditor.dom;
                    let $ = tinymce.dom.DomQuery;

                    const loadingSpinnerId = await addAutocompleteContainer(aikit.prompts[operation].generatedTextPlacement);

                    // remove selection
                    editor.selection.collapse();
                    let spinner = dom.select('#' + loadingSpinnerId);

                    let autocompletedText = '';
                    let postTitle = jQuery('#title').val();
                    try {
                        autocompletedText = await doAutocompleteRequest(operation, selectedText, aikit.selectedLanguage, {
                            postTitle: postTitle,
                            textBeforeSelection: textBeforeSelection,
                            textAfterSelection: textAfterSelection,
                        });
                        autocompletedText = autocompletedText.replace(/\n/g, '<br/>');
                    } catch (error) {
                        // remove the spinner from the editor
                        $(spinner).remove();

                        alert('An API error occurred with the following response body: \n\n' + error.message);
                        return;
                    }

                    // remove the class and id from the spinner
                    $(spinner).removeAttr('class');
                    $(spinner).removeAttr('id');

                    // add the autocompleted text with animation slide down in css
                    $(spinner).html(autocompletedText);

                    // add undo level
                    editor.undoManager.add();
                }
            });
        }

        editor.addButton('aikit_classic_button_text', {
            type: 'menubutton',
            tooltip: 'Generate AI Content using AIKit',
            image: aikit.pluginUrl + 'includes/icons/aikit.svg',
            classes: 'aikit-classic-main-button',
            menu: menu
        });

        const addAutocompleteContainer = async function (placement) {
            let dom = tinymce.activeEditor.dom;
            let $ = tinymce.dom.DomQuery;
            const loadingSpinnerId = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);

            let selectionRange = editor.selection.getRng();
            if (placement === 'below') {
                let selectedNode = editor.selection.getEnd();

                let spinnerHtml = createLoadingSpinner(
                    selectedNode,
                    placement,
                    loadingSpinnerId,
                )
                let spinnerDom = $(spinnerHtml)[0];

                let parentNode = selectionRange.endContainer.parentNode;
                // if parent node is li then we need to create a new li
                if (parentNode.tagName.toLowerCase() === 'li') {
                    $(selectedNode).after(spinnerDom);
                } else if (selectedNode.textContent) {
                    selectionRange.collapse(false);
                    selectionRange.insertNode(spinnerDom);
                    editor.selection.collapse();
                } else {
                    $(selectedNode).after(spinnerDom);
                }

            } else { // above
                let selectedNode = editor.selection.getStart();
                let spinnerHtml = createLoadingSpinner(
                    selectedNode,
                    placement,
                    loadingSpinnerId,
                )
                let spinnerDom = $(spinnerHtml)[0];

                let parentNode = selectionRange.startContainer.parentNode;
                // if parent node is li then we need to create a new li
                if (parentNode.tagName.toLowerCase() === 'li') {
                    $(selectedNode).before(spinnerDom);
                } else if (selectedNode.textContent) {
                    selectionRange.collapse(true);
                    selectionRange.insertNode(spinnerDom);
                    editor.selection.collapse();
                } else {
                    $(selectedNode).before(spinnerDom);
                }
            }

            // add undo level
            editor.undoManager.add();

            return loadingSpinnerId;
        }

        const createLoadingSpinner = function (selectedNode, placement, loadingSpinnerId) {

            let spinnerHtml = '';
            if (['li'].includes(selectedNode.tagName.toLowerCase())) {
                spinnerHtml = '<' + selectedNode.tagName + ' id="' + loadingSpinnerId + '" class="aikit-mce-loading">&nbsp;</' + selectedNode.tagName + '>';
            } else {
                spinnerHtml = '<p id="' + loadingSpinnerId + '" class="aikit-mce-loading">&nbsp;</p>';
            }

            return spinnerHtml;
        }

        const doAutocompleteRequest = async function (requestType, text, selectedLanguage, context) {
            const siteUrl = aikit.siteUrl
            const nonce = aikit.nonce
            const response = await fetch(siteUrl + "/?rest_route=/aikit/openai/v1/autocomplete&type=" + requestType, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce,
                },
                body: JSON.stringify({
                    text: text,
                    context: context,
                    language: selectedLanguage,
                })
            }).catch(async error => {
                throw new Error(await error.text());
            })

            if (!response.ok) {
                throw new Error(await response.text());
            }

            const data = await response.json();

            return data.text
        }


        ///////////////////////////////////////////////////////
        // images
        ///////////////////////////////////////////////////////

        let imageGenerationOptions = aikit.imageGenerationOptions;
        let imageMenu = [];

        for (let size in imageGenerationOptions.sizes) {
            let resolution = imageGenerationOptions.sizes[size];
            for (let count of imageGenerationOptions.counts) {
                imageMenu.push({
                    text: "[" +  tinymce.util.I18n.translate("DALL·E", "aikit") + "] " + count + ' x ' + tinymce.util.I18n.translate(size + ' image(s)') + ' (' + resolution + ')',
                    image: aikit.pluginUrl + 'includes/icons/image-' + size + '.svg',
                    classes: 'aikit-classic-button',
                    onclick: async function () {
                        await onImageGenerationButtonClick(count, size, 'openai');
                    }
                });
            }
        }

        if (aikit.isStabilityAIKeySet) {
            for (let size in aikit.imageGenerationOptions.stabilityAISizes) {
                let resolution = aikit.imageGenerationOptions.stabilityAISizes[size];
                for (let count of aikit.imageGenerationOptions.stabilityAICounts) {
                    imageMenu.push({
                        text: "[" +  tinymce.util.I18n.translate("Stable Diffusion", "aikit") + "] " + count + ' x ' + tinymce.util.I18n.translate(size + " image(s)", "aikit") + ' (' + resolution + ')',
                        image: aikit.pluginUrl + 'includes/icons/image-' + size + '.svg',
                        classes: 'aikit-classic-button',
                        onclick: async function () {
                            await onImageGenerationButtonClick(count, size, 'stability-ai');
                        }
                    });
                }
            }
        }

        if (imageMenu.length > 0) {
            editor.addButton('aikit_classic_button_images', {
                type: 'menubutton',
                tooltip: 'Generate AI Images using AIKit',
                image: aikit.pluginUrl + 'includes/icons/aikit-image.svg',
                classes: 'aikit-classic-main-button',
                menu: imageMenu,
            });
        }

        const onImageGenerationButtonClick = async function (count, size, generator) {
            if (isAiKitProperlyConfigured() === false) {
                return;
            }

            let selectedText = editor.selection.getContent({format: 'text'});
            if (selectedText === '') {
                askUserToSelectText();

                return;
            }

            let dom = tinymce.activeEditor.dom;
            let $ = tinymce.dom.DomQuery;

            const loadingSpinnerId = await addAutocompleteContainer('above');
            let spinner = dom.select('#' + loadingSpinnerId);

            try {
                let result = await doImageGenerationRequest(count, size, selectedText, aikit.selectedLanguage, generator);
                let imagesHtml = '';
                for (let image of result.images) {
                    let imageElementHtml = '<p><img data-mce-src="' + image.url + '" src="' + image.url + '" class="wp-image-' + image.id + '" width="300"  /></p>';
                    imagesHtml += imageElementHtml;
                }

                $(spinner).replaceWith(imagesHtml);

            } catch (error) {
                // remove the spinner from the editor
                $(spinner).remove();

                alert('An API error occurred with the following response body: \n\n' + error.message);
            }
        }

        const doImageGenerationRequest = async function (imageCount, imageSize, text, selectedLanguage, generator) {
            const siteUrl = aikit.siteUrl
            const nonce = aikit.nonce
            const response = await fetch(siteUrl + "/?rest_route=/aikit/" + generator + "/v1/generate-images&count=" + imageCount + "&size=" + imageSize, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce,
                },
                body: JSON.stringify({
                    text: text,
                    language: selectedLanguage,
                })
            }).catch(async error => {
                throw new Error(await error.text());
            })

            if (!response.ok) {
                throw new Error(await response.text());
            }

            const data = await response.json()

            return data
        }


        ///////////////////////////////////////////////////////
        // common
        ///////////////////////////////////////////////////////

        const askUserToSelectText = function (prompt) {
            tinymce.activeEditor.windowManager.alert("This option requires text selection. Please select some text and try again.");
        }

        const isAiKitProperlyConfigured = function () {
            if (aikit.isOpenAIKeyValid === false || aikit.isOpenAIKeyValid === 0 || aikit.isOpenAIKeyValid === '0' || aikit.isOpenAIKeyValid === null) {
                tinymce.activeEditor.windowManager.confirm("It seems that AIKit is not configured correctly. Would you like to configure it now?", function(ok) {
                    if (ok) {
                        window.location.href = aikit.siteUrl + '/wp-admin/options-general.php?page=aikit';
                    }
                });

                return false;
            }

            return true;
        }
    });
})();
