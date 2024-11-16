"use strict";

import { BlockControls } from '@wordpress/block-editor';
import { ToolbarGroup, ToolbarDropdownMenu } from '@wordpress/components';
import { createHigherOrderComponent } from '@wordpress/compose';
import {Fragment, useState} from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import icons from '../icons.js'
import { Button, Modal } from '@wordpress/components';

const allowedBlockTypes = [
    'core/code',
    'core/freeform',
    'core/heading',
    'core/list',
    'core/list-item',
    'core/paragraph',
    'core/preformatted',
];


async function createBlockForAutocompletion(placement = 'below') {
    let selectedBlockClientIds = getSelectedBlockClientIds();
    let [selectionStart, selectionEnd] = getAdjustedSelections(selectedBlockClientIds);
    let lastBlockClientId = selectionEnd.clientId;
    let firstBlockClientId = selectionStart.clientId;
    let lastBlock = wp.data.select('core/block-editor').getBlock(lastBlockClientId);
    let loadingSpinner = createLoadingSpinner();

    if (placement === 'above') {
        let autoCompleteBlock = wp.blocks.createBlock('core/paragraph', {content: loadingSpinner});

        // get index of first block
        let index = wp.data.select('core/block-editor').getBlockIndex(firstBlockClientId);
        // get parent client id of first block
        let parentClientId = wp.data.select('core/block-editor').getBlockRootClientId(firstBlockClientId);

        // insert autocomplete block before the selected block
        await wp.data.dispatch('core/block-editor').insertBlock(
            autoCompleteBlock,
            index,
            parentClientId
        );

        return autoCompleteBlock;
    }

    // if there is more than one block selected or the last block is not a paragraph, add a new autocomplete block at the end.
    if (selectedBlockClientIds.length > 1 || lastBlock.name !== 'core/paragraph' ) { // add a new block after the selected block
        let autoCompleteBlock = wp.blocks.createBlock('core/paragraph', {content: loadingSpinner});
        let parentBlockClientId = wp.data.select('core/block-editor').getBlockRootClientId(lastBlockClientId);
        let indexToInsertAt = wp.data.select('core/block-editor').getBlockIndex(lastBlockClientId) + 1;

        if (!wp.data.select('core/block-editor').canInsertBlockType('core/paragraph', parentBlockClientId)) {
            while (parentBlockClientId) {
                indexToInsertAt = wp.data.select('core/block-editor').getBlockIndex(parentBlockClientId) + 1;
                parentBlockClientId = wp.data.select('core/block-editor').getBlockRootClientId(parentBlockClientId);
                if (wp.data.select('core/block-editor').canInsertBlockType('core/paragraph', parentBlockClientId)) {
                    break;
                }
            }
        }

        // insert after the last block
        await wp.data.dispatch('core/block-editor').insertBlock(
            autoCompleteBlock,
            indexToInsertAt,
            parentBlockClientId
        );

        return autoCompleteBlock;
    }

    let parentBlockClientId = wp.data.select('core/block-editor').getBlockRootClientId(lastBlockClientId);
    if (!wp.data.select('core/block-editor').canInsertBlockType('core/paragraph', parentBlockClientId)) {
        // try to insert the block with every parent block until we find one that works
        while (parentBlockClientId) {
            parentBlockClientId = wp.data.select('core/block-editor').getBlockRootClientId(parentBlockClientId);
            if (wp.data.select('core/block-editor').canInsertBlockType('core/paragraph', parentBlockClientId)) {
                break;
            }
        }

        let autoCompleteBlock = wp.blocks.createBlock('core/paragraph', {content: loadingSpinner});

        // insert the block at the end of the parent block
        await wp.data.dispatch('core/block-editor').insertBlock(autoCompleteBlock, undefined, parentBlockClientId);

        return autoCompleteBlock;
    }

    let lastBlockContent = extractBlockContent(lastBlock);
    let richText = wp.richText.create({html: lastBlockContent});

    let start = 0;
    let end = lastBlockContent.length;

    if ('offset' in selectionEnd) {
        end = selectionEnd.offset;
    }

    let firstPart = wp.richText.slice(richText, start, end);
    let secondPart = wp.richText.slice(richText, end, richText.text.length);

    let firstPartContent = wp.richText.toHTMLString({value: firstPart});
    let secondPartContent = wp.richText.toHTMLString({value: secondPart});

    let inheritedAttributes = lastBlock.attributes;

    // create block with first part
    const key = selectionEnd.attributeKey
    let firstBlockAttributes = inheritedAttributes;
    firstBlockAttributes[key] = firstPartContent;
    const firstPartBlock = wp.blocks.createBlock(lastBlock.name, firstBlockAttributes);

    // create autocomplete block
    let autoCompleteAttributes = inheritedAttributes;
    autoCompleteAttributes[key] = loadingSpinner;
    let autoCompleteBlock = wp.blocks.createBlock('core/paragraph', autoCompleteAttributes);

    // create block with second part
    let secondBlockAttributes = inheritedAttributes;
    secondBlockAttributes[key] = secondPartContent;
    const secondPartBlock = wp.blocks.createBlock(lastBlock.name, secondBlockAttributes);

    let replacementBlocks = [
        firstPartBlock,
        autoCompleteBlock,
        secondPartBlock,
    ];

    if (secondPart.text.trim().length === 0) {
        replacementBlocks = [
            firstPartBlock,
            autoCompleteBlock,
        ];
    }

    // replace the last block with the first part and the second part as a new block
    await wp.data.dispatch('core/block-editor').replaceBlock(lastBlockClientId, replacementBlocks);

    return autoCompleteBlock;
}

function createLoadingSpinner() {
    // generate random id for loading spinner
    const loadingSpinnerId = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
    return '<span id="' + loadingSpinnerId + '" class="aikit-loading"></span>';
}

function getSelectedBlockClientIds() {
    let selectedBlockClientIds = wp.data.select('core/block-editor').getMultiSelectedBlockClientIds();

    if (selectedBlockClientIds.length === 0) {
        selectedBlockClientIds = [wp.data.select('core/block-editor').getSelectedBlockClientId()];
    }

    return selectedBlockClientIds;
}

function getAdjustedSelections(selectedBlockClientIds) {
    const selectionStart = wp.data.select( 'core/block-editor' ).getSelectionStart();
    const selectionEnd = wp.data.select( 'core/block-editor' ).getSelectionEnd();

    if (selectionStart.clientId === selectionEnd.clientId) {
        return [selectionStart, selectionEnd];
    }

    let adjustedSelectionStart = selectionStart;
    let adjustedSelectionEnd = selectionEnd;
    if (selectedBlockClientIds.length > 0 && selectedBlockClientIds[0] === selectionEnd.clientId) {
        adjustedSelectionStart = selectionEnd;
        adjustedSelectionEnd = selectionStart;
    }

    return [adjustedSelectionStart, adjustedSelectionEnd];
}

function extractBlockContent(block) {
    let content = '';
    if ('content' in block.attributes) {
        content = block.attributes.content;
    } else if ('citation' in block.attributes) {
        content = block.attributes.citation;
    } else if ('value' in block.attributes) {
        content = block.attributes.value;
    } else if ('values' in block.attributes) {
        content = block.attributes.values;
    } else if ('text' in block.attributes) {
        content = block.attributes.text;
    }

    return content;
}


function getSelectedBlockContents() {
    let multiSelectedBlockClientIds = getSelectedBlockClientIds();
    let [selectionStart, selectionEnd] = getAdjustedSelections(multiSelectedBlockClientIds);

    let allContent = getAllBlockContentsRecursively(
        multiSelectedBlockClientIds,
        selectionStart,
        selectionEnd
    );

    return allContent.trim();
}

// a function that takes a set of block client ids and returns the content of all of them and all their children recursively as a string
function getAllBlockContentsRecursively(blockClientIds, selectionStart, selectionEnd) {
    let content = '';
    blockClientIds.forEach(blockClientId => {
        const block = wp.data.select( 'core/block-editor' ).getBlock(blockClientId);
        let contentOfBlock = extractBlockContent(block);

        const richText = wp.richText.create( { html: contentOfBlock } );

        let plainText = richText.text;
        let start = 0;
        let end = plainText.length;

        if (selectionStart.clientId === blockClientId && 'offset' in selectionStart) {
            start = selectionStart.offset;
        }

        if (selectionEnd.clientId === blockClientId && 'offset' in selectionEnd) {
            end = selectionEnd.offset;
        }

        plainText = plainText.substring(start, end);

        content += "\n" + plainText;
        if (block.innerBlocks.length > 0) {
            content += getAllBlockContentsRecursively(block.innerBlocks.map(block => block.clientId));
        }
    });

    return content;
}

async function doAutocompleteRequest(requestType, text, selectedLanguage, context) {
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

    const data = await response.json()
    // Todo: handle errors

    return data.text
}

async function doImageGenerationRequest(imageCount, imageSize, text, selectedLanguage, generator) {
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
    // Todo: handle errors

    return data
}


async function autocomplete(requestType, autocompleteBlock, selectedText, textBefore = '', textAfter = '') {
    let autocompletedText = '';
    let selectedLanguage = aikit.selectedLanguage;
    let postTitle = wp.data.select('core/editor').getEditedPostAttribute('title');
    try {
        autocompletedText = await doAutocompleteRequest(requestType, selectedText, selectedLanguage, {
            postTitle: postTitle,
            textBeforeSelection: textBefore,
            textAfterSelection: textAfter,
        });
    } catch (error) {
        // remove the block
        await wp.data.dispatch('core/block-editor').removeBlocks(autocompleteBlock.clientId);

        alert('An API error occurred with the following response body: \n\n' + error.message);
        return;
    }
    const autocompletedTextWithLineBreaks = autocompletedText.replace(/\n/g, '<br>');

    let attributes = autocompleteBlock.attributes;
    attributes.content = autocompletedTextWithLineBreaks;

    if (aikit.autocompletedTextBackgroundColor !== '') {
        let style = attributes.style || {};
        style.color = style.color || {};
        style.color.background = aikit.autocompletedTextBackgroundColor;
        attributes.style = style;
    }

    wp.data.dispatch('core/block-editor').updateBlock(autocompleteBlock.clientId, attributes);
}

async function generateImages(imageCount, imageSize, autocompleteBlock, selectedText, generator = "openai") {
    let selectedLanguage = aikit.selectedLanguage;
    let result = {
        images: [],
    };
    try {
        result = await doImageGenerationRequest(imageCount, imageSize, selectedText, selectedLanguage, generator);
    } catch (error) {
        // remove the block
        await wp.data.dispatch('core/block-editor').removeBlocks(autocompleteBlock.clientId);

        alert('An API error occurred with the following response body: \n\n' + error.message);
        return;
    }

    if (!Array.isArray(result.images)) {
        await wp.data.dispatch('core/block-editor').removeBlocks(autocompleteBlock.clientId);
        alert('An API error occurred with the following response body: \n\n' + result.images);
        return;
    }

    // create a gallery block
    const galleryBlock = wp.blocks.createBlock('core/gallery', {});

    let imageIds = [];
    let imageObjects = [];
    // add images to the gallery block
    result.images.forEach(image => {
        galleryBlock.innerBlocks.push(wp.blocks.createBlock('core/image', {
            url: image.url,
            id: image.id,
        }));
        imageIds.push(image.id);
        imageObjects.push({
            id: image.id,
            fullUrl: image.url,
            url: image.url,
            sizeSlug: 'full',
        });
    });

    // add the image ids to the gallery block attributes
    galleryBlock.attributes.ids = imageIds;
    galleryBlock.attributes.images = imageObjects;

    // replace the autocomplete block with the gallery block
    wp.data.dispatch('core/block-editor').replaceBlocks(autocompleteBlock.clientId, galleryBlock);
}

export default createHigherOrderComponent( ( BlockEdit ) => {
    return ( props ) => {

        // if it's not a text block, return the original block
        if (!allowedBlockTypes.includes(props.name)) {
            return <BlockEdit { ...props } />;
        }

        const [ isSelectionModalOpen, setSelectionModalState ] = useState( false );
        const openSelectionModal = () => setSelectionModalState( true );
        const closeSelectionModal = () => setSelectionModalState( false );

        const [ isSettingsModalOpen, setSettingsModalState ] = useState( false );
        const openSettingsModal = () => setSettingsModalState( true );
        const closeSettingsModal = () => setSettingsModalState( false );

        function getSelectedText() {
            let selectedText = getSelectedBlockContents();

            if (selectedText.length > 0) {
                return selectedText;
            }

            openSelectionModal();

            return false;
        }

        function isProperlyConfigured() {
            if (aikit.isOpenAIKeyValid === undefined || aikit.isOpenAIKeyValid === "0" || aikit.isOpenAIKeyValid === "" || aikit.isOpenAIKeyValid === false) {
                return false;
            }

            return true;
        }

        function goToSettingsPage() {
            window.location.href = '/wp-admin/options-general.php?page=aikit';
        }

        let autocompleteTypes = [];
        Object.keys(aikit.prompts).forEach(function(operationId, index) {
            autocompleteTypes.push({
                label: aikit.prompts[operationId].menuTitle,
                requiresTextSelection: aikit.prompts[operationId].requiresTextSelection,
                operationId: operationId,
                icon: aikit.prompts[operationId].icon,
                generatedTextPlacement: aikit.prompts[operationId].generatedTextPlacement,
            });
        });

        // just so that these are included in the translations
        let smallImageTitle = __("small image(s)", "aikit");
        let mediumImageTitle = __("medium image(s)", "aikit");
        let largeImageTitle = __("large image(s)", "aikit");
        let xlargeLandscapeImageTitle = __("x large (landscape) image(s)", "aikit");
        let xlargePortraitImageTitle = __("large (portrait) image(s)", "aikit");
        let dalle = __("DALL·E", "aikit");
        let stableDiffusion = __("Stable Diffusion", "aikit");

        let imageGenerationOptions = [];
        for (let size in aikit.imageGenerationOptions.sizes) {
            let resolution = aikit.imageGenerationOptions.sizes[size];
            for (let count of aikit.imageGenerationOptions.counts) {
                imageGenerationOptions.push({
                    title: "[" + dalle + "] " + count + ' x ' + __(size + " image(s)", "aikit") + ' (' + resolution + ')',
                    count: count,
                    size: size,
                    generator: "openai",
                });
            }
        }

        // if isStabilityAIKeySet is true, then add the stability options
        if (aikit.isStabilityAIKeySet) {
            for (let size in aikit.imageGenerationOptions.stabilityAISizes) {
                let resolution = aikit.imageGenerationOptions.stabilityAISizes[size];
                for (let count of aikit.imageGenerationOptions.stabilityAICounts) {
                    imageGenerationOptions.push({
                        title: "[" + stableDiffusion + "] " + count + ' x ' + __(size + " image(s)", "aikit") + ' (' + resolution + ')',
                        count: count,
                        size: size,
                        generator: "stability-ai",
                    });
                }
            }
        }

        return (
            <Fragment>
                <BlockEdit { ...props } />
                <BlockControls group="block">
                    <ToolbarGroup>
                        <ToolbarDropdownMenu
                            icon={ icons.aiEdit }
                            label={__("Select how do you want AI to edit your content", "aikit")}
                            controls={ autocompleteTypes.map( ( autocompleteType ) => {
                                return {
                                    title: autocompleteType.label,
                                    icon: icons[autocompleteType.icon],
                                    onClick: async () => {

                                        if (!isProperlyConfigured()) {
                                            openSettingsModal();
                                            return;
                                        }

                                        const placement = autocompleteType.generatedTextPlacement || 'below';

                                        if (autocompleteType.requiresTextSelection) {
                                            const selectedText = getSelectedText();
                                            if (selectedText) {

                                                let startSelectionBlock =  wp.data.select( 'core/block-editor' ).getSelectionStart();
                                                let endSelectionBlock =  wp.data.select( 'core/block-editor' ).getSelectionEnd();

                                                // Get the blocks
                                                let startBlock = wp.data.select('core/block-editor').getBlock(startSelectionBlock.clientId);
                                                let endBlock = wp.data.select('core/block-editor').getBlock(endSelectionBlock.clientId);

                                                // Get the text of the blocks
                                                let startBlockText = startBlock.attributes.content;
                                                let endBlockText = endBlock.attributes.content;

                                                // Get the text before and after the selection
                                                let textBeforeSelection = startBlockText.slice(0, startSelectionBlock.offset);
                                                let textAfterSelection = endBlockText.slice(endSelectionBlock.offset + 1);

                                                const block = await createBlockForAutocompletion(placement);
                                                await autocomplete( autocompleteType.operationId, block, selectedText, textBeforeSelection, textAfterSelection);
                                            }
                                        } else {
                                            const block = await createBlockForAutocompletion(placement);
                                            await autocomplete( autocompleteType.operationId, block, '');
                                        }
                                    },
                                };
                            },)
                        }
                        />
                    </ToolbarGroup>
                    <ToolbarGroup>
                        <ToolbarDropdownMenu
                            icon={ icons.aiKitImage }
                            label={__("Generate AI Images using AIKit", "aikit")}
                            controls={ imageGenerationOptions.map( ( imageGenerationOption ) => {
                                return {
                                    title: imageGenerationOption.title,
                                    icon: icons[imageGenerationOption.size + "Image"],
                                    onClick: async () => {

                                        if (!isProperlyConfigured()) {
                                            openSettingsModal();
                                            return;
                                        }

                                        const placement = 'above';

                                        const selectedText = getSelectedText();
                                        if (selectedText) {
                                            const block = await createBlockForAutocompletion(placement);
                                            await generateImages( imageGenerationOption.count, imageGenerationOption.size, block, selectedText, imageGenerationOption.generator);
                                        }
                                    },
                                };
                            },)
                            }
                        />
                    </ToolbarGroup>
                    { isSelectionModalOpen && (
                        <Modal title={__("Missing Text Selection", 'aikit')} onRequestClose={ closeSelectionModal }>
                            <p>
                                {__('Please make sure to select the text you want to use for AIKit to edit (or operate on).', 'aikit')}
                            </p>
                            <div style={{display: "flex", justifyContent: 'flex-end'}}>
                                <Button variant="primary" className="components-button is-primary" onClick={ closeSelectionModal }  style={{float: 'right'}}>
                                    {__('Ok', 'aikit')}
                                </Button>
                            </div>
                        </Modal>
                    ) }
                    { isSettingsModalOpen && (
                        <Modal title={__("AIKit is not properly configured", 'aikit')} onRequestClose={ closeSettingsModal }>
                            <p>
                                {__('It seems that AIKit is not configured correctly. Please make sure to enter a valid API key in the settings.', 'aikit')}
                            </p>
                            <div style={{display: "flex", justifyContent: 'flex-end'}}>
                                <Button variant="primary" className="components-button is-primary" onClick={ goToSettingsPage }  style={{float: 'right'}}>
                                    {__('Go to settings', 'aikit')}
                                </Button>
                            </div>
                        </Modal>
                    ) }
                </BlockControls>
            </Fragment>
        );
    };
}, 'aiKitControls' );
