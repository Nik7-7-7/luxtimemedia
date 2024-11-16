/*
 *Ultimate Affiliate Pro - Guntengerg Block
 */
"use strict";
var el = wp.element.createElement,
    registerBlockType = wp.blocks.registerBlockType,
    blockStyle = {};

registerBlockType( 'indeed-affiliate-pro/register-form', {
    title                 : 'UAP - Register Form',
    icon                  : 'universal-access-alt',
    category              : 'uap-shortcodes',

    edit: function() {
        return el( 'p', '', '[uap-register]' );
    },
    save: function() {
        return el( 'p', '', '[uap-register]' );
    },
});

registerBlockType( 'indeed-affiliate-pro/login-form', {
    title                 : 'UAP - Login Form',
    icon                  : 'universal-access-alt',
    category              : 'uap-shortcodes',

    edit: function() {
        return el( 'p', '', '[uap-login-form]' );
    },
    save: function() {
        return el( 'p', '', '[uap-login-form]' );
    },
});

registerBlockType( 'indeed-affiliate-pro/logout', {
    title                 : 'UAP - Logout',
    icon                  : 'universal-access-alt',
    category              : 'uap-shortcodes',

    edit: function() {
        return el( 'p', '', '[uap-logout]' );
    },
    save: function() {
        return el( 'p', '', '[uap-logout]' );
    },
});

registerBlockType( 'indeed-affiliate-pro/reset-password', {
    title                 : 'UAP - Reset Password',
    icon                  : 'universal-access-alt',
    category              : 'uap-shortcodes',

    edit: function() {
        return el( 'p', '', '[uap-reset-password]' );
    },
    save: function() {
        return el( 'p', '', '[uap-reset-password]' );
    },
});

registerBlockType( 'indeed-affiliate-pro/account-page', {
    title                 : 'UAP - Account Page',
    icon                  : 'universal-access-alt',
    category              : 'uap-shortcodes',

    edit: function() {
        return el( 'p', '', '[uap-account-page]' );
    },
    save: function() {
        return el( 'p', '', '[uap-account-page]' );
    },
});
