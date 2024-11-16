/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/components/aiKitControls.js":
/*!*****************************************!*\
  !*** ./src/components/aiKitControls.js ***!
  \*****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_compose__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/compose */ "@wordpress/compose");
/* harmony import */ var _wordpress_compose__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_compose__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _icons_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../icons.js */ "./src/icons.js");










const allowedBlockTypes = ['core/code', 'core/freeform', 'core/heading', 'core/list', 'core/list-item', 'core/paragraph', 'core/preformatted'];

async function createBlockForAutocompletion() {
  let placement = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'below';
  let selectedBlockClientIds = getSelectedBlockClientIds();
  let [selectionStart, selectionEnd] = getAdjustedSelections(selectedBlockClientIds);
  let lastBlockClientId = selectionEnd.clientId;
  let firstBlockClientId = selectionStart.clientId;
  let lastBlock = wp.data.select('core/block-editor').getBlock(lastBlockClientId);
  let loadingSpinner = createLoadingSpinner();

  if (placement === 'above') {
    let autoCompleteBlock = wp.blocks.createBlock('core/paragraph', {
      content: loadingSpinner
    }); // get index of first block

    let index = wp.data.select('core/block-editor').getBlockIndex(firstBlockClientId); // get parent client id of first block

    let parentClientId = wp.data.select('core/block-editor').getBlockRootClientId(firstBlockClientId); // insert autocomplete block before the selected block

    await wp.data.dispatch('core/block-editor').insertBlock(autoCompleteBlock, index, parentClientId);
    return autoCompleteBlock;
  } // if there is more than one block selected or the last block is not a paragraph, add a new autocomplete block at the end.


  if (selectedBlockClientIds.length > 1 || lastBlock.name !== 'core/paragraph') {
    // add a new block after the selected block
    let autoCompleteBlock = wp.blocks.createBlock('core/paragraph', {
      content: loadingSpinner
    });
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
    } // insert after the last block


    await wp.data.dispatch('core/block-editor').insertBlock(autoCompleteBlock, indexToInsertAt, parentBlockClientId);
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

    let autoCompleteBlock = wp.blocks.createBlock('core/paragraph', {
      content: loadingSpinner
    }); // insert the block at the end of the parent block

    await wp.data.dispatch('core/block-editor').insertBlock(autoCompleteBlock, undefined, parentBlockClientId);
    return autoCompleteBlock;
  }

  let lastBlockContent = extractBlockContent(lastBlock);
  let richText = wp.richText.create({
    html: lastBlockContent
  });
  let start = 0;
  let end = lastBlockContent.length;

  if ('offset' in selectionEnd) {
    end = selectionEnd.offset;
  }

  let firstPart = wp.richText.slice(richText, start, end);
  let secondPart = wp.richText.slice(richText, end, richText.text.length);
  let firstPartContent = wp.richText.toHTMLString({
    value: firstPart
  });
  let secondPartContent = wp.richText.toHTMLString({
    value: secondPart
  });
  let inheritedAttributes = lastBlock.attributes; // create block with first part

  const key = selectionEnd.attributeKey;
  let firstBlockAttributes = inheritedAttributes;
  firstBlockAttributes[key] = firstPartContent;
  const firstPartBlock = wp.blocks.createBlock(lastBlock.name, firstBlockAttributes); // create autocomplete block

  let autoCompleteAttributes = inheritedAttributes;
  autoCompleteAttributes[key] = loadingSpinner;
  let autoCompleteBlock = wp.blocks.createBlock('core/paragraph', autoCompleteAttributes); // create block with second part

  let secondBlockAttributes = inheritedAttributes;
  secondBlockAttributes[key] = secondPartContent;
  const secondPartBlock = wp.blocks.createBlock(lastBlock.name, secondBlockAttributes);
  let replacementBlocks = [firstPartBlock, autoCompleteBlock, secondPartBlock];

  if (secondPart.text.trim().length === 0) {
    replacementBlocks = [firstPartBlock, autoCompleteBlock];
  } // replace the last block with the first part and the second part as a new block


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
  const selectionStart = wp.data.select('core/block-editor').getSelectionStart();
  const selectionEnd = wp.data.select('core/block-editor').getSelectionEnd();

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
  let allContent = getAllBlockContentsRecursively(multiSelectedBlockClientIds, selectionStart, selectionEnd);
  return allContent.trim();
} // a function that takes a set of block client ids and returns the content of all of them and all their children recursively as a string


function getAllBlockContentsRecursively(blockClientIds, selectionStart, selectionEnd) {
  let content = '';
  blockClientIds.forEach(blockClientId => {
    const block = wp.data.select('core/block-editor').getBlock(blockClientId);
    let contentOfBlock = extractBlockContent(block);
    const richText = wp.richText.create({
      html: contentOfBlock
    });
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
  const siteUrl = aikit.siteUrl;
  const nonce = aikit.nonce;
  const response = await fetch(siteUrl + "/?rest_route=/aikit/openai/v1/autocomplete&type=" + requestType, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': nonce
    },
    body: JSON.stringify({
      text: text,
      context: context,
      language: selectedLanguage
    })
  }).catch(async error => {
    throw new Error(await error.text());
  });

  if (!response.ok) {
    throw new Error(await response.text());
  }

  const data = await response.json(); // Todo: handle errors

  return data.text;
}

async function doImageGenerationRequest(imageCount, imageSize, text, selectedLanguage, generator) {
  const siteUrl = aikit.siteUrl;
  const nonce = aikit.nonce;
  const response = await fetch(siteUrl + "/?rest_route=/aikit/" + generator + "/v1/generate-images&count=" + imageCount + "&size=" + imageSize, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': nonce
    },
    body: JSON.stringify({
      text: text,
      language: selectedLanguage
    })
  }).catch(async error => {
    throw new Error(await error.text());
  });

  if (!response.ok) {
    throw new Error(await response.text());
  }

  const data = await response.json(); // Todo: handle errors

  return data;
}

async function autocomplete(requestType, autocompleteBlock, selectedText) {
  let textBefore = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : '';
  let textAfter = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : '';
  let autocompletedText = '';
  let selectedLanguage = aikit.selectedLanguage;
  let postTitle = wp.data.select('core/editor').getEditedPostAttribute('title');

  try {
    autocompletedText = await doAutocompleteRequest(requestType, selectedText, selectedLanguage, {
      postTitle: postTitle,
      textBeforeSelection: textBefore,
      textAfterSelection: textAfter
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

async function generateImages(imageCount, imageSize, autocompleteBlock, selectedText) {
  let generator = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : "openai";
  let selectedLanguage = aikit.selectedLanguage;
  let result = {
    images: []
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
  } // create a gallery block


  const galleryBlock = wp.blocks.createBlock('core/gallery', {});
  let imageIds = [];
  let imageObjects = []; // add images to the gallery block

  result.images.forEach(image => {
    galleryBlock.innerBlocks.push(wp.blocks.createBlock('core/image', {
      url: image.url,
      id: image.id
    }));
    imageIds.push(image.id);
    imageObjects.push({
      id: image.id,
      fullUrl: image.url,
      url: image.url,
      sizeSlug: 'full'
    });
  }); // add the image ids to the gallery block attributes

  galleryBlock.attributes.ids = imageIds;
  galleryBlock.attributes.images = imageObjects; // replace the autocomplete block with the gallery block

  wp.data.dispatch('core/block-editor').replaceBlocks(autocompleteBlock.clientId, galleryBlock);
}

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ((0,_wordpress_compose__WEBPACK_IMPORTED_MODULE_3__.createHigherOrderComponent)(BlockEdit => {
  return props => {
    // if it's not a text block, return the original block
    if (!allowedBlockTypes.includes(props.name)) {
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(BlockEdit, props);
    }

    const [isSelectionModalOpen, setSelectionModalState] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);

    const openSelectionModal = () => setSelectionModalState(true);

    const closeSelectionModal = () => setSelectionModalState(false);

    const [isSettingsModalOpen, setSettingsModalState] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);

    const openSettingsModal = () => setSettingsModalState(true);

    const closeSettingsModal = () => setSettingsModalState(false);

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
    Object.keys(aikit.prompts).forEach(function (operationId, index) {
      autocompleteTypes.push({
        label: aikit.prompts[operationId].menuTitle,
        requiresTextSelection: aikit.prompts[operationId].requiresTextSelection,
        operationId: operationId,
        icon: aikit.prompts[operationId].icon,
        generatedTextPlacement: aikit.prompts[operationId].generatedTextPlacement
      });
    }); // just so that these are included in the translations

    let smallImageTitle = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__.__)("small image(s)", "aikit");

    let mediumImageTitle = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__.__)("medium image(s)", "aikit");

    let largeImageTitle = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__.__)("large image(s)", "aikit");

    let xlargeLandscapeImageTitle = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__.__)("x large (landscape) image(s)", "aikit");

    let xlargePortraitImageTitle = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__.__)("large (portrait) image(s)", "aikit");

    let dalle = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__.__)("DALL·E", "aikit");

    let stableDiffusion = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__.__)("Stable Diffusion", "aikit");

    let imageGenerationOptions = [];

    for (let size in aikit.imageGenerationOptions.sizes) {
      let resolution = aikit.imageGenerationOptions.sizes[size];

      for (let count of aikit.imageGenerationOptions.counts) {
        imageGenerationOptions.push({
          title: "[" + dalle + "] " + count + ' x ' + (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__.__)(size + " image(s)", "aikit") + ' (' + resolution + ')',
          count: count,
          size: size,
          generator: "openai"
        });
      }
    } // if isStabilityAIKeySet is true, then add the stability options


    if (aikit.isStabilityAIKeySet) {
      for (let size in aikit.imageGenerationOptions.stabilityAISizes) {
        let resolution = aikit.imageGenerationOptions.stabilityAISizes[size];

        for (let count of aikit.imageGenerationOptions.stabilityAICounts) {
          imageGenerationOptions.push({
            title: "[" + stableDiffusion + "] " + count + ' x ' + (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__.__)(size + " image(s)", "aikit") + ' (' + resolution + ')',
            count: count,
            size: size,
            generator: "stability-ai"
          });
        }
      }
    }

    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(BlockEdit, props), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.BlockControls, {
      group: "block"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToolbarGroup, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToolbarDropdownMenu, {
      icon: _icons_js__WEBPACK_IMPORTED_MODULE_5__["default"].aiEdit,
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__.__)("Select how do you want AI to edit your content", "aikit"),
      controls: autocompleteTypes.map(autocompleteType => {
        return {
          title: autocompleteType.label,
          icon: _icons_js__WEBPACK_IMPORTED_MODULE_5__["default"][autocompleteType.icon],
          onClick: async () => {
            if (!isProperlyConfigured()) {
              openSettingsModal();
              return;
            }

            const placement = autocompleteType.generatedTextPlacement || 'below';

            if (autocompleteType.requiresTextSelection) {
              const selectedText = getSelectedText();

              if (selectedText) {
                let startSelectionBlock = wp.data.select('core/block-editor').getSelectionStart();
                let endSelectionBlock = wp.data.select('core/block-editor').getSelectionEnd(); // Get the blocks

                let startBlock = wp.data.select('core/block-editor').getBlock(startSelectionBlock.clientId);
                let endBlock = wp.data.select('core/block-editor').getBlock(endSelectionBlock.clientId); // Get the text of the blocks

                let startBlockText = startBlock.attributes.content;
                let endBlockText = endBlock.attributes.content; // Get the text before and after the selection

                let textBeforeSelection = startBlockText.slice(0, startSelectionBlock.offset);
                let textAfterSelection = endBlockText.slice(endSelectionBlock.offset + 1);
                const block = await createBlockForAutocompletion(placement);
                await autocomplete(autocompleteType.operationId, block, selectedText, textBeforeSelection, textAfterSelection);
              }
            } else {
              const block = await createBlockForAutocompletion(placement);
              await autocomplete(autocompleteType.operationId, block, '');
            }
          }
        };
      })
    })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToolbarGroup, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToolbarDropdownMenu, {
      icon: _icons_js__WEBPACK_IMPORTED_MODULE_5__["default"].aiKitImage,
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__.__)("Generate AI Images using AIKit", "aikit"),
      controls: imageGenerationOptions.map(imageGenerationOption => {
        return {
          title: imageGenerationOption.title,
          icon: _icons_js__WEBPACK_IMPORTED_MODULE_5__["default"][imageGenerationOption.size + "Image"],
          onClick: async () => {
            if (!isProperlyConfigured()) {
              openSettingsModal();
              return;
            }

            const placement = 'above';
            const selectedText = getSelectedText();

            if (selectedText) {
              const block = await createBlockForAutocompletion(placement);
              await generateImages(imageGenerationOption.count, imageGenerationOption.size, block, selectedText, imageGenerationOption.generator);
            }
          }
        };
      })
    })), isSelectionModalOpen && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Modal, {
      title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__.__)("Missing Text Selection", 'aikit'),
      onRequestClose: closeSelectionModal
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__.__)('Please make sure to select the text you want to use for AIKit to edit (or operate on).', 'aikit')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      style: {
        display: "flex",
        justifyContent: 'flex-end'
      }
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
      variant: "primary",
      className: "components-button is-primary",
      onClick: closeSelectionModal,
      style: {
        float: 'right'
      }
    }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__.__)('Ok', 'aikit')))), isSettingsModalOpen && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Modal, {
      title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__.__)("AIKit is not properly configured", 'aikit'),
      onRequestClose: closeSettingsModal
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__.__)('It seems that AIKit is not configured correctly. Please make sure to enter a valid API key in the settings.', 'aikit')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      style: {
        display: "flex",
        justifyContent: 'flex-end'
      }
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
      variant: "primary",
      className: "components-button is-primary",
      onClick: goToSettingsPage,
      style: {
        float: 'right'
      }
    }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__.__)('Go to settings', 'aikit'))))));
  };
}, 'aiKitControls'));

/***/ }),

/***/ "./src/icons.js":
/*!**********************!*\
  !*** ./src/icons.js ***!
  \**********************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);



const icons = {};
icons.aiEdit = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
  viewBox: "0 0 1024 1024",
  xmlns: "http://www.w3.org/2000/svg",
  xmlSpace: "preserve",
  style: {
    fillRule: "evenodd",
    clipRule: "evenodd",
    strokeLinejoin: "round",
    strokeMiterlimit: 2
  },
  width: "24",
  height: "24"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M747.217 741.073H592.046l-17.654-91.058H464.75l-17.654 91.058H299.358L405.283 164.06h236.009l105.925 577.013ZM556.738 541.302l-32.521-206.275h-6.504l-32.521 206.275h71.546ZM804.825 164.06h157.958v577.013H804.825z",
  style: {
    fillRule: "nonzero"
  },
  transform: "translate(-325.7 6.797) scale(1.26841)"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("g", {
  transform: "matrix(.96486 -.98402 .98402 .96486 357.274 523.994)"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("clipPath", {
  id: "a"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M0 0h512v512H0z"
})), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("g", {
  clipPath: "url(#a)"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M358.752 51.159 70.113 339.798l102.063 102.064L455.088 158.95 512 .282 358.752 51.159ZM172.176 419.287l-79.488-79.489L349.536 82.949l.069 11.622 22.52-.089.089 22.667 22.625.048-.048 22.691 22.467-.271.27 22.847 11.538-.068-256.89 256.891Zm270.472-272.936-9.345.055-.27-22.946-22.244.269.048-22.464-22.721-.048-.091-22.764-22.551.089-.068-11.463 1.963-1.963 75.812-25.169 4.761 23.811 22.704 4.541-27.998 78.052Zm18.858-96.156-3.062-15.313 27.457-9.116-9.808 27.346-14.587-2.917Z",
  style: {
    fillRule: "nonzero"
  }
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M154.944 458.57 53.127 356.751l11.287-11.286L166.23 447.282z"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M10.388 399.522c-13.851 13.852-13.851 36.391 0 50.244l51.546 51.545c6.711 6.711 15.632 10.406 25.122 10.406s18.412-3.696 25.122-10.406l37.073-37.073L47.462 362.45l-37.074 37.072Zm90.503 90.503a19.433 19.433 0 0 1-13.834 5.731 19.437 19.437 0 0 1-13.834-5.731L21.676 438.48c-7.629-7.628-7.629-20.042 0-27.67l25.785-25.785 79.215 79.216-25.785 25.784Z",
  style: {
    fillRule: "nonzero"
  }
}))));
icons.aiKitImage = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
  viewBox: "0 0 1024 1024",
  xmlns: "http://www.w3.org/2000/svg",
  xmlSpace: "preserve",
  style: {
    fillRule: "evenodd",
    clipRule: "evenodd",
    strokeLinejoin: "round",
    strokeMiterlimit: 2
  },
  width: "24",
  height: "24"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M747.217 741.073H592.046l-17.654-91.058H464.75l-17.654 91.058H299.358L405.283 164.06h236.009l105.925 577.013ZM556.738 541.302l-32.521-206.275h-6.504l-32.521 206.275h71.546Z",
  style: {
    fillRule: "nonzero"
  },
  transform: "matrix(.99899 0 0 .9533 -127.254 205.777)"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  style: {
    fillRule: "nonzero"
  },
  d: "M804.825 164.06h157.958v577.013H804.825z",
  transform: "matrix(.96507 0 0 .87429 -91.232 266.1)"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("g", {
  transform: "scale(1.04856 .94992) rotate(-45 948.682 -122.154)"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("clipPath", {
  id: "a"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M0 0h512v512H0z"
})), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("g", {
  clipPath: "url(#a)"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M358.752 51.159 70.113 339.798l102.063 102.064L455.088 158.95 512 .282 358.752 51.159ZM172.176 419.287l-79.488-79.489L349.536 82.949l.069 11.622 22.52-.089.089 22.667 22.625.048-.048 22.691 22.467-.271.27 22.847 11.538-.068-256.89 256.891Zm270.472-272.936-9.345.055-.27-22.946-22.244.269.048-22.464-22.721-.048-.091-22.764-22.551.089-.068-11.463 1.963-1.963 75.812-25.169 4.761 23.811 22.704 4.541-27.998 78.052Zm18.858-96.156-3.062-15.313 27.457-9.116-9.808 27.346-14.587-2.917Z",
  style: {
    fillRule: "nonzero"
  }
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M154.944 458.57 53.127 356.751l11.287-11.286L166.23 447.282z"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M10.388 399.522c-13.851 13.852-13.851 36.391 0 50.244l51.546 51.545c6.711 6.711 15.632 10.406 25.122 10.406s18.412-3.696 25.122-10.406l37.073-37.073L47.462 362.45l-37.074 37.072Zm90.503 90.503a19.433 19.433 0 0 1-13.834 5.731 19.437 19.437 0 0 1-13.834-5.731L21.676 438.48c-7.629-7.628-7.629-20.042 0-27.67l25.785-25.785 79.215 79.216-25.785 25.784Z",
  style: {
    fillRule: "nonzero"
  }
}))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("g", {
  transform: "matrix(1.57564 0 0 1.28 -119.349 0)"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("clipPath", {
  id: "b"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M0 0h800v800H0z"
})), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("g", {
  clipPath: "url(#b)"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M591.27 0H304.889l-14.553 14.553L89.094 215.784l-14.553 14.554v435.453c0 74 60.198 134.211 134.211 134.211H591.27c73.989 0 134.189-60.211 134.189-134.211V134.213C725.459 60.2 665.259 0 591.27 0Zm84.507 665.789c0 46.683-37.835 84.517-84.507 84.517H208.752c-46.672 0-84.507-37.834-84.507-84.517V250.923h130.808c38.881 0 70.411-31.528 70.411-70.421V49.694H591.27c46.672 0 84.507 37.834 84.507 84.519v531.576Z",
  style: {
    fillRule: "nonzero"
  }
}))));
icons.smallImage = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
  viewBox: "0 0 1024 1024",
  xmlns: "http://www.w3.org/2000/svg",
  xmlSpace: "preserve",
  style: {
    fillRule: "evenodd",
    clipRule: "evenodd",
    strokeLinejoin: "round",
    strokeMiterlimit: 2
  },
  width: "24",
  height: "24"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M41.106 75.092c0-15.253 12.367-27.62 27.62-27.62H234.45c15.255 0 27.621 12.367 27.621 27.62 0 15.255-12.366 27.621-27.621 27.621H96.347v138.103c0 15.255-12.366 27.621-27.621 27.621-15.253 0-27.62-12.366-27.62-27.621V75.092ZM952.588 47.472c15.252 0 27.62 12.367 27.62 27.62v165.724c0 15.255-12.368 27.621-27.62 27.621-15.253 0-27.621-12.366-27.621-27.621V102.713H786.864c-15.253 0-27.621-12.366-27.621-27.621 0-15.253 12.368-27.62 27.621-27.62h165.724ZM68.726 986.574c-15.253 0-27.62-12.368-27.62-27.62V793.23c0-15.253 12.367-27.621 27.62-27.621 15.255 0 27.621 12.368 27.621 27.621v138.103H234.45c15.255 0 27.621 12.368 27.621 27.621 0 15.252-12.366 27.62-27.621 27.62H68.726ZM980.208 958.954c0 15.252-12.368 27.62-27.62 27.62H786.864c-15.253 0-27.621-12.368-27.621-27.62 0-15.253 12.368-27.621 27.621-27.621h138.103V793.23c0-15.253 12.368-27.621 27.621-27.621 15.252 0 27.62 12.368 27.62 27.621v165.724Z"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M235.552 775.063c-16.153-19.88-24.229-37.741-24.229-53.583 0-15.842 13.357-36.809 40.071-62.902 15.531-14.91 31.995-22.365 49.39-22.365s41.624 15.531 72.687 46.594c8.697 10.561 21.122 20.657 37.275 30.286 16.152 9.63 31.063 14.444 44.73 14.444 57.777 0 86.665-23.607 86.665-70.823 0-14.289-7.921-26.248-23.763-35.877-15.842-9.63-35.567-16.619-59.174-20.968-23.608-4.348-49.079-11.338-76.415-20.967-27.335-9.629-52.806-20.967-76.414-34.014-23.608-13.046-43.332-33.703-59.174-61.97-15.842-28.267-23.763-62.281-23.763-102.041 0-54.67 20.346-102.352 61.038-143.044 40.692-40.692 96.139-61.038 166.341-61.038 37.275 0 71.289 4.815 102.041 14.444 30.752 9.629 52.03 19.414 63.834 29.354l23.297 17.706c19.259 18.016 28.888 33.237 28.888 45.662 0 12.425-7.455 29.51-22.365 51.254-21.123 31.063-42.866 46.594-65.232 46.594-13.046 0-29.199-6.213-48.458-18.638-1.863-1.242-5.436-4.348-10.716-9.319-5.281-4.97-10.096-9.008-14.444-12.114-13.047-8.076-29.665-12.114-49.856-12.114-20.191 0-36.965 4.814-50.322 14.444-13.357 9.629-20.035 22.986-20.035 40.071 0 17.084 7.921 30.907 23.763 41.468 15.842 10.562 35.567 17.706 59.174 21.434 23.608 3.727 49.39 9.474 77.347 17.24 27.956 7.765 53.738 17.239 77.346 28.422 23.607 11.183 43.332 30.597 59.174 58.243 15.842 27.645 23.763 61.659 23.763 102.041 0 40.381-8.076 75.948-24.229 106.7-16.152 30.752-37.275 54.515-63.368 71.289-50.321 32.927-104.06 49.39-161.215 49.39-29.199 0-56.845-3.572-82.938-10.717-26.092-7.144-47.215-15.997-63.368-26.558-32.926-19.88-55.913-39.139-68.959-57.777l-8.387-10.251Z",
  style: {
    fillRule: "nonzero"
  },
  transform: "matrix(.93883 0 0 .93883 80.45 15.755)"
}));
icons.mediumImage = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
  viewBox: "0 0 1024 1024",
  xmlns: "http://www.w3.org/2000/svg",
  xmlSpace: "preserve",
  style: {
    fillRule: "evenodd",
    clipRule: "evenodd",
    strokeLinejoin: "round",
    strokeMiterlimit: 2
  },
  width: "24",
  height: "24"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M41.106 75.092c0-15.253 12.367-27.62 27.62-27.62H234.45c15.255 0 27.621 12.367 27.621 27.62 0 15.255-12.366 27.621-27.621 27.621H96.347v138.103c0 15.255-12.366 27.621-27.621 27.621-15.253 0-27.62-12.366-27.62-27.621V75.092ZM952.588 47.472c15.252 0 27.62 12.367 27.62 27.62v165.724c0 15.255-12.368 27.621-27.62 27.621-15.253 0-27.621-12.366-27.621-27.621V102.713H786.864c-15.253 0-27.621-12.366-27.621-27.621 0-15.253 12.368-27.62 27.621-27.62h165.724ZM68.726 986.574c-15.253 0-27.62-12.368-27.62-27.62V793.23c0-15.253 12.367-27.621 27.62-27.621 15.255 0 27.621 12.368 27.621 27.621v138.103H234.45c15.255 0 27.621 12.368 27.621 27.621 0 15.252-12.366 27.62-27.621 27.62H68.726ZM980.208 958.954c0 15.252-12.368 27.62-27.62 27.62H786.864c-15.253 0-27.621-12.368-27.621-27.62 0-15.253 12.368-27.621 27.621-27.621h138.103V793.23c0-15.253 12.368-27.621 27.621-27.621 15.252 0 27.62 12.368 27.62 27.621v165.724Z"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M951.238 214.07c4.97 9.319 7.921 17.706 8.852 25.161.932 7.455 1.398 18.327 1.398 32.616v517.195c0 42.866-11.804 68.027-35.411 75.482-11.804 3.727-26.714 5.591-44.731 5.591-18.016 0-32.305-1.553-42.866-4.659s-18.638-6.834-24.229-11.183c-5.591-4.349-9.94-10.561-13.046-18.637-3.107-10.562-4.66-26.714-4.66-48.458V474.997c-15.531 19.259-38.207 49.856-68.027 91.791-29.821 41.934-48.769 68.182-56.845 78.744a11488.045 11488.045 0 0 1-16.774 21.899c-3.106 4.038-10.717 9.629-22.831 16.774-12.115 7.144-25.161 10.716-39.139 10.716-13.978 0-26.714-3.261-38.207-9.784-11.494-6.524-19.725-12.891-24.695-19.104l-7.455-10.251c-12.425-15.531-36.965-48.613-73.619-99.245-36.654-50.632-56.534-77.812-59.64-81.54v314.045c0 14.288-.466 25.005-1.398 32.149-.932 7.145-3.883 15.066-8.853 23.763-9.319 16.774-33.548 25.161-72.687 25.161-37.896 0-61.504-8.387-70.823-25.161-4.97-8.697-7.921-16.773-8.853-24.229-.932-7.455-1.398-18.637-1.398-33.547V269.983c0-14.289.466-25.005 1.398-32.15.932-7.144 3.883-15.376 8.853-24.695 9.319-16.152 33.548-24.229 72.687-24.229 16.774 0 31.218 2.019 43.332 6.058 12.115 4.038 20.036 8.231 23.763 12.58l5.592 5.591 211.537 277.701C698.076 351.057 768.589 258.8 804 214.07c10.562-16.774 35.567-25.161 75.017-25.161 39.449 0 63.523 8.387 72.221 25.161Z",
  style: {
    fillRule: "nonzero"
  },
  transform: "matrix(.82218 0 0 .82218 24.124 76.647)"
}));
icons.largeImage = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
  viewBox: "0 0 1024 1024",
  xmlns: "http://www.w3.org/2000/svg",
  xmlSpace: "preserve",
  style: {
    fillRule: "evenodd",
    clipRule: "evenodd",
    strokeLinejoin: "round",
    strokeMiterlimit: 2
  },
  width: "24",
  height: "24"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M41.106 75.092c0-15.253 12.367-27.62 27.62-27.62H234.45c15.255 0 27.621 12.367 27.621 27.62 0 15.255-12.366 27.621-27.621 27.621H96.347v138.103c0 15.255-12.366 27.621-27.621 27.621-15.253 0-27.62-12.366-27.62-27.621V75.092ZM952.588 47.472c15.252 0 27.62 12.367 27.62 27.62v165.724c0 15.255-12.368 27.621-27.62 27.621-15.253 0-27.621-12.366-27.621-27.621V102.713H786.864c-15.253 0-27.621-12.366-27.621-27.621 0-15.253 12.368-27.62 27.621-27.62h165.724ZM68.726 986.574c-15.253 0-27.62-12.368-27.62-27.62V793.23c0-15.253 12.367-27.621 27.62-27.621 15.255 0 27.621 12.368 27.621 27.621v138.103H234.45c15.255 0 27.621 12.368 27.621 27.621 0 15.252-12.366 27.62-27.621 27.62H68.726ZM980.208 958.954c0 15.252-12.368 27.62-27.62 27.62H786.864c-15.253 0-27.621-12.368-27.621-27.62 0-15.253 12.368-27.621 27.621-27.621h138.103V793.23c0-15.253 12.368-27.621 27.621-27.621 15.252 0 27.62 12.368 27.62 27.621v165.724Z"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M390.244 733.129h253.472c20.502 0 35.412 2.329 44.731 6.989 9.319 4.659 15.531 11.959 18.637 21.899 3.107 9.94 4.66 23.142 4.66 39.605 0 16.463-1.553 29.665-4.66 39.605-3.106 9.94-8.387 16.774-15.842 20.501-11.803 6.213-27.956 9.319-48.457 9.319H307.307c-43.488 0-68.959-11.804-76.414-35.411-3.728-9.94-5.592-25.782-5.592-47.526V269.983c0-14.289.466-25.005 1.398-32.15.932-7.144 3.883-15.376 8.853-24.695 8.698-16.774 32.927-25.161 72.687-25.161 43.488 0 69.27 11.494 77.346 34.48 3.106 10.561 4.659 26.714 4.659 48.458v462.214Z",
  style: {
    fillRule: "nonzero"
  },
  transform: "matrix(.98497 0 0 .98497 49.178 -9.552)"
}));
icons["xlarge landscapeImage"] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  fillRule: "evenodd",
  strokeLinejoin: "round",
  strokeMiterlimit: "2",
  clipRule: "evenodd",
  viewBox: "0 0 1024 1024"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M41.106 75.092c0-15.253 12.367-27.62 27.62-27.62H234.45c15.255 0 27.621 12.367 27.621 27.62 0 15.255-12.366 27.621-27.621 27.621H96.347v138.103c0 15.255-12.366 27.621-27.621 27.621-15.253 0-27.62-12.366-27.62-27.621V75.092zM952.588 47.472c15.252 0 27.62 12.367 27.62 27.62v165.724c0 15.255-12.368 27.621-27.62 27.621-15.253 0-27.621-12.366-27.621-27.621V102.713H786.864c-15.253 0-27.621-12.366-27.621-27.621 0-15.253 12.368-27.62 27.621-27.62h165.724zM68.726 986.574c-15.253 0-27.62-12.368-27.62-27.62V793.23c0-15.253 12.367-27.621 27.62-27.621 15.255 0 27.621 12.368 27.621 27.621v138.103H234.45c15.255 0 27.621 12.368 27.621 27.621 0 15.252-12.366 27.62-27.621 27.62H68.726zM980.208 958.954c0 15.252-12.368 27.62-27.62 27.62H786.864c-15.253 0-27.621-12.368-27.621-27.62 0-15.253 12.368-27.621 27.621-27.621h138.103V793.23c0-15.253 12.368-27.621 27.621-27.621 15.252 0 27.62 12.368 27.62 27.621v165.724z"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("g", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("text", {
  x: "248.765",
  y: "833.107",
  fontFamily: "'ArialRoundedMTBold', 'Arial Rounded MT Bold', sans-serif",
  fontSize: "900"
}, "X")));
icons["xlarge landscape Image"] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  fillRule: "evenodd",
  strokeLinejoin: "round",
  strokeMiterlimit: "2",
  clipRule: "evenodd",
  viewBox: "0 0 1024 1024"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M41.106 75.092c0-15.253 12.367-27.62 27.62-27.62H234.45c15.255 0 27.621 12.367 27.621 27.62 0 15.255-12.366 27.621-27.621 27.621H96.347v138.103c0 15.255-12.366 27.621-27.621 27.621-15.253 0-27.62-12.366-27.62-27.621V75.092zM952.588 47.472c15.252 0 27.62 12.367 27.62 27.62v165.724c0 15.255-12.368 27.621-27.62 27.621-15.253 0-27.621-12.366-27.621-27.621V102.713H786.864c-15.253 0-27.621-12.366-27.621-27.621 0-15.253 12.368-27.62 27.621-27.62h165.724zM68.726 986.574c-15.253 0-27.62-12.368-27.62-27.62V793.23c0-15.253 12.367-27.621 27.62-27.621 15.255 0 27.621 12.368 27.621 27.621v138.103H234.45c15.255 0 27.621 12.368 27.621 27.621 0 15.252-12.366 27.62-27.621 27.62H68.726zM980.208 958.954c0 15.252-12.368 27.62-27.62 27.62H786.864c-15.253 0-27.621-12.368-27.621-27.62 0-15.253 12.368-27.621 27.621-27.621h138.103V793.23c0-15.253 12.368-27.621 27.621-27.621 15.252 0 27.62 12.368 27.62 27.621v165.724z"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("g", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("text", {
  x: "248.765",
  y: "833.107",
  fontFamily: "'ArialRoundedMTBold', 'Arial Rounded MT Bold', sans-serif",
  fontSize: "900"
}, "X")));
icons["xlarge portraitImage"] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  fillRule: "evenodd",
  strokeLinejoin: "round",
  strokeMiterlimit: "2",
  clipRule: "evenodd",
  viewBox: "0 0 1024 1024"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M41.106 75.092c0-15.253 12.367-27.62 27.62-27.62H234.45c15.255 0 27.621 12.367 27.621 27.62 0 15.255-12.366 27.621-27.621 27.621H96.347v138.103c0 15.255-12.366 27.621-27.621 27.621-15.253 0-27.62-12.366-27.62-27.621V75.092zM952.588 47.472c15.252 0 27.62 12.367 27.62 27.62v165.724c0 15.255-12.368 27.621-27.62 27.621-15.253 0-27.621-12.366-27.621-27.621V102.713H786.864c-15.253 0-27.621-12.366-27.621-27.621 0-15.253 12.368-27.62 27.621-27.62h165.724zM68.726 986.574c-15.253 0-27.62-12.368-27.62-27.62V793.23c0-15.253 12.367-27.621 27.62-27.621 15.255 0 27.621 12.368 27.621 27.621v138.103H234.45c15.255 0 27.621 12.368 27.621 27.621 0 15.252-12.366 27.62-27.621 27.62H68.726zM980.208 958.954c0 15.252-12.368 27.62-27.62 27.62H786.864c-15.253 0-27.621-12.368-27.621-27.62 0-15.253 12.368-27.621 27.621-27.621h138.103V793.23c0-15.253 12.368-27.621 27.621-27.621 15.252 0 27.62 12.368 27.62 27.621v165.724z"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("g", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("text", {
  x: "248.765",
  y: "833.107",
  fontFamily: "'ArialRoundedMTBold', 'Arial Rounded MT Bold', sans-serif",
  fontSize: "900"
}, "X")));
icons.troll = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
  width: "24",
  height: "24",
  viewBox: "0 0 165 138",
  version: "1.1",
  xmlns: "http://www.w3.org/2000/svg",
  xmlSpace: "preserve",
  style: {
    fillRule: 'evenodd',
    clipRule: 'evenodd',
    strokeLinejoin: 'round',
    strokeMiterlimit: 2
  }
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("use", {
  id: "Background",
  xlinkHref: "#_Image1",
  x: "0",
  y: "0",
  width: "220px",
  height: "183px",
  transform: "matrix(1,0,0,1.00182,0,0)"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("defs", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("image", {
  id: "_Image1",
  width: "165px",
  height: "137px",
  xlinkHref: "data:image/png;base64,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9mxt165dWNO0o1wxuq7TL774Qvrwww/j4XD4OAcgIYQahsF0XWfJwgGnTBsghNAtW7bow4cPV1q0aGEMHjwYl5eXH/WBZKUKIsuyumPHDt8LL7xQ3aFDB68oihoc71Khx/oDnU4nHT9+vBEOh8/KJabrOlu8eDG54oorSKof9BQXtdlsamlpafS6664LTps2LfD1118Htm3bFq6vr49JkiRrmmYYhkEwxtSsz36houKTKb9N96eUsqqqKnLrrbfqbdu2jc6aNctrpqioqqo/88wzXqvVaqZOUEiUGjwqzE2EhLquSRlcbdy4cY379++P3X///eqwYcMChw8fjrIT+B+TW33GpEmT5JKSEk0QhDMZ1KbLZrPRdu3akXHjxknLly+PBgIBxcztwRg3pZCeDwzDMNauXRvo27dvnOd5Cgk/K1myZMkpHXMYY+3QoUOBt99+2zto0KBIdna2xnHccf4+hBArKCigzzzzDDnXPB2MMauoqMDTp0+Pd+7cOSaKonHscwRBMIqKimJjxowJfPTRR/7y8vJILBZTCSGY/UY5SSfCkSNH2KuvvopnzJiB16xZQxobGykhhNbW1uK//vWvavfu3UN33323b82aNZqu6+zgwYPxvn37hlLGshYAhpkSkkMIDQCAClMSiKKIR48eHayqqor/4x//UPv06RNZvXp1+ETJG7qus2+//Zb07dsX2+32Y8lIk9IE8zxvWCwW3WKx6KIoGgghfKzkEUWRtGrVSr7hhhsCH374YaCmpiaOE1sk5zzYlFIaDAbl999/v6Fjx45BQRAwADCe59m1116LDx8+fCbbGFTXdXzkyBHls88+i914443xvLw83XRaW61W0qtXL/2jjz7CgUDgvKWRLMv4p59+iowePbqB53kjhZB4+PDh3hUrVgSDwaB6vmPTnFBVlS1btgzfcsstSufOnbXRo0drX331lRqPx0k8Hifr1q2LjBgxonbAgAHK9u3bqaZp+OWXXw7b7XZzk0NFCE0zw5NKAWBZKkEuueSS8M8//xzetWsX7tq1q/Lqq6/6VFU9Tn1qmsYWLlxISktLj43IoTabTS0pKQkPGjTI9/DDDzf85S9/qXv99ddr33jjjbpXXnmlYdy4cfWXXnppvSiKKhwvdajNZtO6desWePnll+v37NkTUlVVZWc5Abqua5s2bfLffvvtDW63W0l9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5MiRmiiKBABYr1691FAo1EQGVVWpqqrnZChSSllFRQV++OGH8eTJk42amho56UPFDQ0N4eeff/5IWlpaU/lBjuNov379wuXl5SfMMj3TZ8ZiMZrIyUu85Pf7488888zhpHlw1Li1aNEi/s9//vNQ69atmzReWVmZsXfvXoMQwmpqavDu3bu1SCSCT7Sg2rhxI23fvn0qB+oAYAgcC4SQAxJFCJqy/QoKCuSvvvoqZBgGxhizH374Affp00cZOHCg//PPPw94vV6FEEIxxlSSpDMaE13X6fz58+NFRUXSSVQsEwRB69Gjh2/mzJkNu3fvDiuKcpz9Rymln376acDlcmmm0VS3aAAAIABJREFUyfHtt982Hvs+QojR0NCgfP/99/L06dPlkSNHqh06dNAyMjLwKZL9qc1m0zp06BC78847w2+88UZkxYoVserqajmp5rHP5zPmzJmjjh071vj222+bLcVVkiT65JNP6larlQiCQHr27BmaOnWq/5lnngn27t3bb7Vam9SoKIpswIAB+pYtW6TmyImnlNJwOKwuXrw4OGLECL/FYjmhl4PneTxmzJj6QYMGNZpjmJ+fj9esWXPahCdVVdkTTzxBrVareT8dAP4FADnHkTKJfAD4EpKF9TmOI1deeWV4586dIZbIeaZbtmzB9913n37JJZfERo8eHVm8eLEUj8fPai9LkiR91qxZvmPTXY+Vmk6nU+3evXto+vTpjeXl5SGMcZNap5TShQsX+jwej5ocKOPOO++slWVZOtEzKaVUVVXS0NBgbN68WZs3b57y0EMPxbp27Rrz/L/2vjy8qupc/1t7PHNOTgYSkpAAASIQCDPKqCCDgANWcMCBilf7Q6o8tZVWqVd6e9UrV6239tY6UpWqzKCgcJEyyyAIGGYykjk58zl7XGv9/th7x0NISILYqvV9nvUQTnb2Xnut76y1vun9PB7ZzD+/qB8Mw2Cn06nm5ubGx40bF/n5z38efOedd/zHjx8PBQIBye/3ax3RYDuKaDRK58+f35z3hBDCgiBoPM/riSu9KIp0+vTp5ODBg/rlKEotIcuysnv37sBDDz3UlJOTE2NZVk+cC9Ns0/x8r9cbz8rKClq5UElJSfq6devk9p5z/Phx0rNnz8TFqBoAZsKliNYQQoMA4IjVAYQQnjhxYlV9fb3furEkSXTTpk3atGnTlK5d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"
})));
icons.baby = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 299.831 299.831",
  style: {
    enableBackground: "new 0 0 299.831 299.831"
  },
  xmlSpace: "preserve",
  width: 24,
  height: 24
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M271.201 117.716c-14.252-53.638-63.223-93.282-121.285-93.282S42.883 64.078 28.63 117.717C12.533 119.604 0 133.32 0 149.915s12.533 30.312 28.63 32.199c14.252 53.639 63.223 93.282 121.286 93.282s107.033-39.644 121.286-93.282c16.096-1.887 28.63-15.603 28.63-32.199s-12.534-30.311-28.631-32.199zm-121.285 133.98c-56.122 0-101.78-45.659-101.78-101.78 0-36.482 19.298-68.537 48.218-86.509-.013.461-.07.924-.182 1.383a6.55 6.55 0 0 1-2.978 4.08c-5.587 3.408-7.354 10.7-3.945 16.287a11.842 11.842 0 0 0 16.287 3.946c6.91-4.215 11.765-10.867 13.67-18.733 1.402-5.79 1.078-11.726-.87-17.212 2.765-.905 5.58-1.699 8.444-2.367 6.397 16.568.246 35.897-15.441 45.466-5.587 3.408-7.354 10.701-3.945 16.287a11.844 11.844 0 0 0 16.288 3.945c23.77-14.5 34.126-42.731 27.14-68.341 4.633.04 9.193.392 13.661 1.035 6.74 25.922-4.286 53.831-27.536 68.013-5.587 3.408-7.354 10.701-3.945 16.287a11.844 11.844 0 0 0 16.288 3.945c28.328-17.28 43.534-49.283 40.427-81.186 36.4 15.524 61.981 51.667 61.981 93.673-.002 56.123-45.66 101.781-101.782 101.781z"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M129.222 159.272c0-6.544-5.306-11.85-11.85-11.85H103.65c-6.544 0-11.85 5.306-11.85 11.85s5.306 11.85 11.85 11.85h13.721c6.545 0 11.851-5.306 11.851-11.85zM196.181 147.422H182.46c-6.544 0-11.85 5.306-11.85 11.85s5.306 11.85 11.85 11.85h13.721c6.544 0 11.85-5.306 11.85-11.85s-5.306-11.85-11.85-11.85zM182.257 200.809c-4.938-4.294-12.423-3.775-16.718 1.164a20.7 20.7 0 0 1-15.623 7.123 20.7 20.7 0 0 1-15.623-7.123c-4.296-4.938-11.781-5.458-16.718-1.164-4.938 4.295-5.459 11.78-1.164 16.718a44.396 44.396 0 0 0 67.01 0c4.295-4.937 3.773-12.422-1.164-16.718z"
}));
icons.idea = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 66.383 66.383",
  style: {
    enableBackground: "new 0 0 66.383 66.383"
  },
  xmlSpace: "preserve",
  width: 24,
  height: 24
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M33.265.002 33.191 0l-.074.002C21.841.28 9.704 7.697 9.704 23.324c0 8.91 5.177 14.091 8.957 17.875 1.317 1.317 3.121 3.123 3.191 3.893l.25 2.727h22.18l.249-2.727c.07-.77 1.874-2.575 3.191-3.893 3.78-3.784 8.957-8.965 8.957-17.875C56.68 7.698 44.542.281 33.265.002zm10.214 36.956c-1.661 1.662-3.171 3.173-4.075 4.86H26.979c-.904-1.688-2.414-3.198-4.074-4.86-3.375-3.379-7.202-7.208-7.202-13.634 0-11.635 8.771-17.078 17.489-17.322 8.717.245 17.487 5.687 17.487 17.322.001 6.426-3.825 10.256-7.2 13.634zM21.192 53.835c0 6.919 5.383 12.548 12 12.548s12-5.629 12-12.548v-3h-24v3zm17.332 3c-.998 2.105-3.014 3.548-5.332 3.548s-4.334-1.442-5.333-3.548h10.665z"
}));
icons.summary = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 501.999 501.999",
  style: {
    enableBackground: "new 0 0 501.999 501.999"
  },
  xmlSpace: "preserve"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M371.5 52.138c-48.289 0-99.403 8.434-120.5 26.564-21.097-18.131-72.211-26.564-120.5-26.564C67.647 52.138 0 66.424 0 97.786V439.86c0 5.523 4.477 10 10 10a9.92 9.92 0 0 0 1.163-.074h238.669c.052.006.103.017.155.023a10.25 10.25 0 0 0 2.024 0c.053-.005.103-.017.156-.023h238.669c.382.044.769.074 1.163.074 5.523 0 10-4.477 10-10V97.786C502 66.424 434.353 52.138 371.5 52.138zM33.073 429.786c3.882-1.808 8.834-3.706 15.136-5.566 21.855-6.454 51.08-10.008 82.291-10.008s60.436 3.554 82.291 10.008c6.302 1.86 11.253 3.758 15.136 5.566H33.073zM241 414.023c-24.733-13.481-68.702-19.811-110.5-19.811s-85.767 6.33-110.5 19.811V97.786c0-1.448 5.006-8.777 28.13-15.618 21.864-6.468 51.117-10.03 82.37-10.03s60.506 3.562 82.37 10.03C235.994 89.01 241 96.338 241 97.786v316.237zm33.073 15.763c3.882-1.808 8.834-3.706 15.136-5.566 21.855-6.454 51.08-10.008 82.291-10.008s60.436 3.554 82.291 10.008c6.302 1.86 11.253 3.758 15.136 5.566H274.073zM482 414.023c-24.733-13.481-68.702-19.811-110.5-19.811s-85.767 6.33-110.5 19.811V97.786c0-1.448 5.006-8.777 28.13-15.618 21.864-6.468 51.117-10.03 82.37-10.03s60.506 3.562 82.37 10.03C476.994 89.01 482 96.338 482 97.786v316.237z"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M100.77 129.187a388.04 388.04 0 0 1 29.73-1.123c28.459 0 55.799 3.033 76.984 8.54a10.02 10.02 0 0 0 2.522.324c4.444 0 8.501-2.985 9.671-7.487 1.39-5.345-1.817-10.805-7.163-12.194-22.781-5.922-51.908-9.183-82.016-9.183-10.588 0-21.109.397-31.27 1.182-5.506.425-9.626 5.233-9.201 10.74.428 5.506 5.243 9.636 10.743 9.201zM50.993 136.929c.833 0 1.679-.105 2.522-.324 4.476-1.164 9.297-2.232 14.331-3.178 5.428-1.02 9.001-6.247 7.982-11.674-1.02-5.428-6.248-9.003-11.674-7.982a236.148 236.148 0 0 0-15.669 3.477c-5.346 1.389-8.553 6.849-7.164 12.194 1.171 4.5 5.228 7.487 9.672 7.487zM212.516 168.969c-22.779-5.922-51.906-9.183-82.016-9.183-30.11 0-59.237 3.261-82.016 9.183-5.345 1.39-8.552 6.849-7.162 12.194 1.39 5.345 6.85 8.551 12.194 7.162 21.183-5.507 48.523-8.54 76.984-8.54s55.801 3.033 76.984 8.54c.844.219 1.69.324 2.523.324 4.444 0 8.501-2.985 9.671-7.486 1.39-5.345-1.817-10.804-7.162-12.194zM212.516 220.664c-22.779-5.922-51.906-9.183-82.016-9.183-30.11 0-59.237 3.261-82.016 9.183-5.345 1.39-8.552 6.849-7.162 12.194 1.39 5.345 6.85 8.551 12.194 7.162 21.183-5.507 48.523-8.54 76.984-8.54s55.801 3.033 76.984 8.54c.844.219 1.69.324 2.523.324 4.444 0 8.501-2.985 9.671-7.486 1.39-5.345-1.817-10.804-7.162-12.194zM53.515 291.743c21.186-5.507 48.525-8.54 76.984-8.54 28.459 0 55.799 3.033 76.984 8.54a10.02 10.02 0 0 0 2.522.324c4.444 0 8.501-2.985 9.671-7.487 1.392-5.346-1.815-10.805-7.16-12.194-22.781-5.922-51.908-9.183-82.016-9.183-30.108 0-59.235 3.261-82.016 9.183-5.345 1.389-8.552 6.849-7.163 12.194 1.389 5.345 6.85 8.553 12.194 7.163zM212.516 324.109c-22.779-5.922-51.906-9.183-82.016-9.183s-59.237 3.261-82.016 9.183c-5.345 1.39-8.552 6.849-7.162 12.194 1.39 5.345 6.85 8.552 12.194 7.162 21.183-5.507 48.523-8.54 76.984-8.54s55.801 3.033 76.984 8.54c.844.219 1.69.324 2.523.324 4.444 0 8.501-2.985 9.671-7.486 1.39-5.345-1.817-10.806-7.162-12.194zM294.516 136.604c21.186-5.507 48.525-8.54 76.984-8.54 28.459 0 55.799 3.033 76.984 8.54a10.02 10.02 0 0 0 2.522.324c4.444 0 8.501-2.985 9.671-7.487 1.39-5.345-1.817-10.805-7.163-12.194-22.781-5.922-51.908-9.183-82.016-9.183-30.108 0-59.235 3.261-82.016 9.183-5.345 1.389-8.552 6.849-7.163 12.194 1.392 5.345 6.852 8.553 12.197 7.163zM453.516 168.971c-22.779-5.922-51.906-9.183-82.016-9.183s-59.237 3.261-82.016 9.183c-5.345 1.39-8.552 6.849-7.162 12.194 1.39 5.345 6.849 8.551 12.194 7.162 21.183-5.507 48.523-8.54 76.984-8.54s55.801 3.033 76.984 8.54c.844.219 1.69.324 2.523.324 4.444 0 8.501-2.985 9.671-7.486 1.39-5.345-1.817-10.806-7.162-12.194zM453.516 220.664c-22.779-5.922-51.906-9.183-82.016-9.183s-59.237 3.261-82.016 9.183c-5.345 1.39-8.552 6.849-7.162 12.194 1.39 5.345 6.849 8.552 12.194 7.162 21.183-5.507 48.523-8.54 76.984-8.54s55.801 3.033 76.984 8.54c.844.219 1.69.324 2.523.324 4.444 0 8.501-2.985 9.671-7.486 1.39-5.345-1.817-10.804-7.162-12.194zM294.515 291.743c21.186-5.507 48.525-8.54 76.984-8.54 28.459 0 55.799 3.033 76.984 8.54a10.02 10.02 0 0 0 2.522.324c4.444 0 8.501-2.985 9.671-7.487 1.392-5.346-1.815-10.805-7.16-12.194-22.781-5.922-51.908-9.183-82.016-9.183-30.108 0-59.235 3.261-82.016 9.183-5.345 1.389-8.552 6.849-7.163 12.194 1.389 5.345 6.849 8.553 12.194 7.163zM453.516 324.109c-22.779-5.922-51.906-9.183-82.016-9.183s-59.237 3.261-82.016 9.183c-5.345 1.39-8.552 6.849-7.162 12.194 1.39 5.345 6.849 8.552 12.194 7.162 21.183-5.507 48.523-8.54 76.984-8.54s55.801 3.033 76.984 8.54c.844.219 1.69.324 2.523.324 4.444 0 8.501-2.985 9.671-7.486 1.39-5.345-1.817-10.806-7.162-12.194z"
}));
icons.zip = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 512.009 512.009",
  style: {
    enableBackground: "new 0 0 512.009 512.009"
  },
  xmlSpace: "preserve",
  width: 24,
  height: 24
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M443.738 17.076c-15.497 0-30.336 2.714-44.237 7.492l-6.238-18.731c-1.485-4.463-6.315-6.895-10.795-5.393a8.543 8.543 0 0 0-5.402 10.795l6.648 19.925a135.937 135.937 0 0 0-20.036 12.083l-15.147-15.138a8.523 8.523 0 0 0-12.066 0c-3.337 3.328-3.337 8.73 0 12.066l14.003 13.995c-6.519 6.127-12.442 12.851-17.664 20.139l-19.703-13.133c-3.934-2.62-9.225-1.562-11.836 2.364-2.611 3.917-1.553 9.225 2.364 11.836l20.011 13.338c-3.703 6.827-6.741 14.046-9.25 21.504l-20.19-7.305a8.515 8.515 0 0 0-10.923 5.129c-1.604 4.429.691 9.327 5.12 10.923l21.495 7.765c-1.408 6.997-2.133 14.216-2.423 21.547-19.959-7.689-39.194-10.513-47.121-11.366-1.417-.247-2.867-.367-4.343-.367s-2.927.119-4.335.367c-7.936.853-27.17 3.678-47.138 11.366-.282-7.194-.998-14.276-2.355-21.146l22.596-8.166c4.429-1.596 6.724-6.494 5.12-10.923a8.522 8.522 0 0 0-10.923-5.129l-21.222 7.672c-2.458-7.381-5.47-14.515-9.105-21.282l20.898-13.926c3.917-2.611 4.975-7.919 2.364-11.836a8.528 8.528 0 0 0-11.836-2.364l-20.48 13.653a137.343 137.343 0 0 0-17.442-20.104l14.558-14.549c3.337-3.337 3.337-8.738 0-12.066-3.336-3.337-8.738-3.337-12.066 0l-15.642 15.642a135.283 135.283 0 0 0-19.703-12.1l6.81-20.412A8.542 8.542 0 0 0 130.702.446a8.517 8.517 0 0 0-10.795 5.393l-6.366 19.115c-14.191-5.009-29.389-7.876-45.269-7.876a8.53 8.53 0 0 0-8.533 8.533 8.536 8.536 0 0 0 8.533 8.533c65.877 0 119.467 53.589 119.467 119.467 0 .768.247 1.459.435 2.167-16.111 8.977-27.767 23.74-32.828 41.822-5.299 18.978-2.526 39.552 7.612 56.457l34.057 56.747c.393 14.012 1.169 70.153-10.428 94.754-5.069 10.743-7.014 21.231-5.777 31.155 5.973 47.846 33.604 75.298 75.819 75.298s69.854-27.452 75.81-75.298c1.246-9.924-.7-20.412-5.768-31.155-12.271-26.018-10.684-87.211-10.359-96.947l32.742-54.554c10.138-16.905 12.911-37.478 7.603-56.457-5.052-18.082-16.708-32.845-32.828-41.822.196-.708.444-1.399.444-2.167 0-65.877 53.589-119.467 119.467-119.467a8.534 8.534 0 0 0-.002-17.068zM216 241.016c-.58.683-1.195 1.323-1.741 2.039-1.681 2.193-3.243 4.506-4.599 6.989-1.929 3.524-3.567 7.296-5.06 11.196-.239.64-.495 1.254-.734 1.903-1.374 3.84-2.534 7.868-3.507 12.049-.154.683-.299 1.374-.444 2.065-.273 1.28-.597 2.526-.828 3.84l-21.495-35.831c-7.74-12.885-9.847-28.587-5.803-43.068 3.925-14.046 13.065-25.463 25.728-32.145 11.042-5.82 22.989-9.634 33.229-12.117a26.303 26.303 0 0 0-.341 4.207v66.893c-.529.299-.998.674-1.519.99-.887.529-1.732 1.101-2.586 1.681a54.09 54.09 0 0 0-4.19 3.2c-.777.657-1.562 1.314-2.304 2.022-1.348 1.28-2.594 2.662-3.806 4.087zm74.138 206.729c0 11.904-9.685 21.598-21.598 21.598h-25.071c-11.913 0-21.598-9.694-21.598-21.598v-25.079c0-11.904 9.685-21.589 21.598-21.589h25.071c11.913 0 21.598 9.685 21.598 21.589v25.079zm50.082-245.547c4.045 14.481 1.937 30.182-5.803 43.068l-20.557 34.261c-.145-.759-.341-1.459-.503-2.202-.188-.888-.393-1.758-.597-2.62-.956-4.07-2.082-8.004-3.422-11.742-.196-.546-.427-1.067-.631-1.604-1.502-3.951-3.166-7.757-5.111-11.315-1.425-2.611-3.063-5.026-4.838-7.313-.58-.742-1.229-1.399-1.843-2.099-1.28-1.485-2.594-2.918-4.028-4.233-.785-.734-1.613-1.399-2.449-2.082a51.294 51.294 0 0 0-4.403-3.226c-.922-.597-1.835-1.186-2.799-1.724-.555-.324-1.058-.717-1.63-1.015v-66.21c0-1.442-.119-2.85-.35-4.216 10.231 2.475 22.17 6.289 33.237 12.126 12.663 6.682 21.802 18.1 25.727 32.146z"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M268.541 418.14H243.47a4.534 4.534 0 0 0-4.531 4.531v25.071c0 2.5 2.031 4.531 4.531 4.531h25.071a4.54 4.54 0 0 0 4.531-4.531v-25.071a4.54 4.54 0 0 0-4.531-4.531z"
}));
icons.laugh = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
  width: 24,
  height: 24,
  viewBox: "-8 0 512 512",
  xmlns: "http://www.w3.org/2000/svg"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm141.4 389.4c-37.8 37.8-88 58.6-141.4 58.6s-103.6-20.8-141.4-58.6S48 309.4 48 256s20.8-103.6 58.6-141.4S194.6 56 248 56s103.6 20.8 141.4 58.6S448 202.6 448 256s-20.8 103.6-58.6 141.4zM328 224c17.7 0 32-14.3 32-32s-14.3-32-32-32-32 14.3-32 32 14.3 32 32 32zm-160 0c17.7 0 32-14.3 32-32s-14.3-32-32-32-32 14.3-32 32 14.3 32 32 32zm194.4 64H133.6c-8.2 0-14.5 7-13.5 15 7.5 59.2 58.9 105 121.1 105h13.6c62.2 0 113.6-45.8 121.1-105 1-8-5.3-15-13.5-15z"
}));
icons.megaphone = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 392.58 392.58",
  style: {
    enableBackground: "new 0 0 392.58 392.58"
  },
  xmlSpace: "preserve",
  width: 24,
  height: 24
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M161.413 268.135c-6.012 0-10.925 4.848-10.925 10.925v32.97c0 6.012 4.849 10.925 10.925 10.925a10.87 10.87 0 0 0 10.925-10.925v-32.97c-.065-6.076-4.913-10.925-10.925-10.925z"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M322.318 97.986V10.843C321.154-1.117 310.423-.923 306.867.952L137.041 80.337H32.249c-6.012 0-10.925 4.849-10.925 10.925V222.56c0 6.012 4.849 10.925 10.925 10.925h9.826L72.2 383.853a10.865 10.865 0 0 0 10.667 8.727H142.924c6.012 0 10.925-4.848 10.925-10.925 0-1.228-29.608-148.234-29.608-148.234h12.735l169.762 79.386c12.865 3.168 15.451-6.206 15.451-9.891v-87.143c27.927-5.172 49.067-29.608 49.067-58.958.129-29.286-21.011-53.657-48.938-58.829zM129.542 370.729H91.853L64.314 233.42h37.689l27.539 137.309zm1.164-159.095H43.174V102.123h87.531v109.511zm169.826 74.085-147.976-69.172v-34.392h4.461c6.012 0 10.925-4.849 10.925-10.925 0-6.012-4.848-10.925-10.925-10.925h-4.461v-21.786h26.246c6.012 0 10.925-4.848 10.925-10.925 0-6.012-4.849-10.925-10.925-10.925h-26.246V97.016l147.976-69.172v257.875zm21.786-92.25v-73.115c15.774 4.719 27.281 19.329 27.281 36.525 0 17.26-11.507 31.871-27.281 36.59z"
}));
icons.tongue = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
  width: 512,
  height: 512,
  viewBox: "-8 0 512 512",
  xmlns: "http://www.w3.org/2000/svg"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm64 400c0 35.6-29.1 64.5-64.9 64-35.1-.5-63.1-29.8-63.1-65v-42.8l17.7-8.8c15-7.5 31.5 1.7 34.9 16.5l2.8 12.1c2.1 9.2 15.2 9.2 17.3 0l2.8-12.1c3.4-14.8 19.8-24.1 34.9-16.5l17.7 8.8V408zm28.2 25.3c2.2-8.1 3.8-16.5 3.8-25.3v-43.5c14.2-12.4 24.4-27.5 27.3-44.5 1.7-9.9-7.7-18.5-17.7-15.3-25.9 8.3-64.4 13.1-105.6 13.1s-79.6-4.8-105.6-13.1c-9.9-3.1-19.4 5.3-17.7 15.3 2.9 17 13.1 32.1 27.3 44.5V408c0 8.8 1.6 17.2 3.8 25.3C91.8 399.9 48 333 48 256c0-110.3 89.7-200 200-200s200 89.7 200 200c0 77-43.8 143.9-107.8 177.3zM168 176c-17.7 0-32 14.3-32 32s14.3 32 32 32 32-14.3 32-32-14.3-32-32-32zm160 0c-17.7 0-32 14.3-32 32s14.3 32 32 32 32-14.3 32-32-14.3-32-32-32z"
}));
icons.funnel = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
  width: 200,
  height: 200,
  "data-name": "Layer 1",
  xmlns: "http://www.w3.org/2000/svg"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("title", null), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M110.22 117.75h-80a10 10 0 0 0 0 20h80a10 10 0 0 0 0-20ZM177.22 125.75a9.67 9.67 0 0 0-14 0l-8 7.5v-90.5a10 10 0 0 0-20 0v113.5a8.29 8.29 0 0 0 3 8 9.67 9.67 0 0 0 14 0l24.5-24.5a10.13 10.13 0 0 0 .5-14ZM110.22 37.75h-80a10 10 0 0 0 0 20h80a10 10 0 0 0 0-20ZM30.22 97.75h70a10 10 0 0 0 0-20h-70a10 10 0 0 0 0 20Z"
}));
icons.paraphrase = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
  viewBox: "0 0 459 459",
  xmlns: "http://www.w3.org/2000/svg",
  xmlSpace: "preserve",
  style: {
    fillRule: "evenodd",
    clipRule: "evenodd",
    strokeLinejoin: "round",
    strokeMiterlimit: 2
  },
  width: 24,
  height: 24
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M294.371 392.558h.002a2.342 2.342 0 0 1-2.339 2.339H68.644a2.342 2.342 0 0 1-2.339-2.339V65.461c0-1.29 1.049-2.34 2.339-2.34h223.39c1.29 0 2.339 1.05 2.339 2.34v22.788l24.476-23.62c-.442-14.414-12.297-26.006-26.815-26.006H68.644c-14.798 0-26.837 12.039-26.837 26.838v327.097c0 14.797 12.038 26.837 26.836 26.837h223.39c14.798 0 26.836-12.039 26.836-26.837V277.414l-24.498 24.497v90.647Z",
  style: {
    fillRule: "nonzero"
  }
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M84.613 325.861c0 6.764 5.484 12.249 12.249 12.249h42.837a36.526 36.526 0 0 1-5.255-24.497H96.862v-.001c-6.765 0-12.249 5.484-12.249 12.249ZM147.641 252.136H96.862c-6.765 0-12.249 5.485-12.249 12.249 0 6.765 5.484 12.249 12.249 12.249h45.472l5.307-24.498ZM191.96 190.661H96.862c-6.765 0-12.249 5.485-12.249 12.25 0 6.764 5.484 12.249 12.249 12.249h70.6l24.498-24.499ZM253.435 129.187H96.862c-6.765 0-12.249 5.484-12.249 12.249 0 6.764 5.484 12.249 12.249 12.249h132.075l24.498-24.498ZM401.763 88.757c-20.055-20.648-52.704-20.549-72.707-.546L177.35 239.917a12.334 12.334 0 0 0-3.31 6.068l-15.171 70.036a12.252 12.252 0 0 0 14.565 14.565l70.036-15.171a12.492 12.492 0 0 0 6.068-3.31l151.705-151.707c19.887-19.885 19.854-51.745.52-71.641ZM186.838 302.616l6.39-29.498 23.108 23.108-29.498 6.39Zm197.083-159.541L240.876 286.12l-37.542-37.543 143.044-143.045c10.701-10.701 28.207-10.346 38.455.96 9.405 10.363 9.196 26.475-.912 36.583Z",
  style: {
    fillRule: "nonzero"
  }
}));
icons.summaryConcise = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
  width: "1em",
  height: "1em",
  viewBox: "0 0 21 21",
  xmlns: "http://www.w3.org/2000/svg"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("g", {
  fill: "none",
  fillRule: "evenodd",
  stroke: "#000",
  strokeLinecap: "round",
  strokeLinejoin: "round"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M4.5 6.5h12M7.498 10.5h5.997M5.5 14.5h9.995"
})));
icons.paragraph = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
  height: "1em",
  width: "1em",
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 512 512",
  xmlSpace: "preserve"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M0 451h512v-64H0v64zm0-106.7h512v-64H0v64zm0-106.6h512v-64H0v64zM0 67v64h512V67H0z"
}));
icons.pencil = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
  height: "1em",
  width: "1em",
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 512 512",
  xmlSpace: "preserve"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "m70.2 337.4 104.4 104.4L441.5 175 337 70.5 70.2 337.4zM.6 499.8c-2.3 9.3 2.3 13.9 11.6 11.6L151.4 465 47 360.6.6 499.8zM487.9 24.1c-46.3-46.4-92.8-11.6-92.8-11.6-7.6 5.8-34.8 34.8-34.8 34.8l104.4 104.4s28.9-27.2 34.8-34.8c0 0 34.8-46.3-11.6-92.8z"
}));
icons.title = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
  height: "1em",
  width: "1em",
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 512 512",
  xmlSpace: "preserve"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M46.5 0v139.6h23.3c0-23.3 0-69.8 23.3-93.1 23.2-23.3 46.5-23.3 69.8-23.3h46.5v395.6c0 34.9-11.6 69.8-46.5 69.8h-22.8l-.5 23.2h232.7v-23.3H349c-34.9 0-46.5-34.9-46.5-69.8V23.3H349c23.3 0 46.5 0 69.8 23.3s23.3 69.8 23.3 93.1h23.3V0H46.5z"
}));
icons.summarize = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
  width: "1em",
  height: "1em",
  viewBox: "0 -3 20 20",
  xmlns: "http://www.w3.org/2000/svg"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M4 7h12m-9 6h6M1 1h18",
  stroke: "#000",
  strokeWidth: 2,
  fill: "none",
  fillRule: "evenodd",
  strokeLinecap: "round",
  strokeLinejoin: "round"
}));
icons.image = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
  height: "1em",
  width: "1em",
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  xmlSpace: "preserve"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M24 22H0V2h24v20zM3.4 20H22v-2.6l-5-5-5 5-3-3L3.4 20zM2 4v14.6l7-7 3 3 5-5 5 5V4H2z"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("circle", {
  cx: 7,
  cy: 8,
  r: 2
}));
icons.bulletPoints = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
  height: "1em",
  width: "1em",
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 32 32",
  xmlSpace: "preserve"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M11 8h18c.6 0 1-.4 1-1s-.4-1-1-1H11c-.6 0-1 .4-1 1s.4 1 1 1zM11 17h11c.6 0 1-.4 1-1s-.4-1-1-1H11c-.6 0-1 .4-1 1s.4 1 1 1zM29 24H11c-.6 0-1 .4-1 1s.4 1 1 1h18c.6 0 1-.4 1-1s-.4-1-1-1zM5 4C3.3 4 2 5.3 2 7s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3zM5 13c-1.7 0-3 1.3-3 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3zM5 22c-1.7 0-3 1.3-3 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"
}));
icons.quote = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
  width: "1em",
  height: "1em",
  viewBox: "0 0 48 48",
  xmlSpace: "preserve",
  xmlns: "http://www.w3.org/2000/svg"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("g", {
  fill: "#241F20"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M18.686 6.513H.001v16.35h10.628c-.098 10.181-9.584 12.104-9.584 12.104s-.05.341 0 6.521c15.815-3.034 17.499-14.931 17.636-18.625h.004v-.102c.021-.632 0-1.028 0-1.028V6.513zM47.99 21.732V6.513H29.306v16.35h10.629c-.098 10.181-9.584 12.104-9.584 12.104s-.05.341 0 6.521c15.815-3.034 17.499-14.931 17.636-18.625h.004v-.102c.02-.632-.001-1.029-.001-1.029z"
})));
icons.custom = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
  width: "1em",
  height: "1em",
  viewBox: "0 0 24 24",
  xmlns: "http://www.w3.org/2000/svg"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  fill: "none",
  d: "M0 0h24v24H0z"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zM6 6h5v5H6V6zm4.5 13a2.5 2.5 0 0 1 0-5 2.5 2.5 0 0 1 0 5zm3-6 3-5 3 5h-6z"
}));
icons.article = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  xmlSpace: "preserve",
  width: "1em",
  height: "1em"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M20.5 22h-17C1.6 22 0 20.4 0 18.5V6h5V2h19v16.5c0 1.9-1.6 3.5-3.5 3.5zM6.7 20h13.8c.8 0 1.5-.7 1.5-1.5V4H7v14.5c0 .5-.1 1-.3 1.5zM2 8v10.5c0 .8.7 1.5 1.5 1.5S5 19.3 5 18.5V8H2z"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
  d: "M15 6h5v6h-5zM9 6h4v2H9zM9 10h4v2H9zM9 14h11v2H9z"
}));
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (icons);

/***/ }),

/***/ "./src/index.js":
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/hooks */ "@wordpress/hooks");
/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_hooks__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./style.scss */ "./src/style.scss");
/* harmony import */ var _components_aiKitControls__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./components/aiKitControls */ "./src/components/aiKitControls.js");





(0,_wordpress_hooks__WEBPACK_IMPORTED_MODULE_0__.addFilter)('editor.BlockEdit', 'aikit/controls', _components_aiKitControls__WEBPACK_IMPORTED_MODULE_2__["default"]);

/***/ }),

/***/ "./src/style.scss":
/*!************************!*\
  !*** ./src/style.scss ***!
  \************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "@wordpress/block-editor":
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
/***/ ((module) => {

module.exports = window["wp"]["blockEditor"];

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ ((module) => {

module.exports = window["wp"]["components"];

/***/ }),

/***/ "@wordpress/compose":
/*!*********************************!*\
  !*** external ["wp","compose"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["compose"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/hooks":
/*!*******************************!*\
  !*** external ["wp","hooks"] ***!
  \*******************************/
/***/ ((module) => {

module.exports = window["wp"]["hooks"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["i18n"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var chunkIds = deferred[i][0];
/******/ 				var fn = deferred[i][1];
/******/ 				var priority = deferred[i][2];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"index": 0,
/******/ 			"./style-index": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var chunkIds = data[0];
/******/ 			var moreModules = data[1];
/******/ 			var runtime = data[2];
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunkaikit"] = self["webpackChunkaikit"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["./style-index"], () => (__webpack_require__("./src/index.js")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;
//# sourceMappingURL=index.js.map