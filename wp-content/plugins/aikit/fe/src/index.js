"use strict";

import { addFilter } from '@wordpress/hooks';
import './style.scss';
import aiKitControls from "./components/aiKitControls";

addFilter(
	'editor.BlockEdit',
	'aikit/controls',
	aiKitControls,
);

