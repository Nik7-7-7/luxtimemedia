const { defineConfig } = require('@vue/cli-service');
const path = require('path');

module.exports = defineConfig({
  transpileDependencies: true,
  filenameHashing: false,
  devServer: {
    proxy: {
      '/wp-': {
          target: 'http://ssa.localdev',
          changeOrigin: true
      },
      '/getApi.php': {
          target: 'http://ssa.localdev/wp-content/plugins/simply-schedule-appointments',
          changeOrigin: true
      }
    }
  },

  assetsDir: 'static',

  configureWebpack: {
    resolve: {
      alias: {
        'highlight.js': path.resolve(__dirname, 'node_modules/highlight.js'),
      },
    },
  },
  chainWebpack: (config) => {
    config.plugin('define').tap((definitions) => {
      Object.assign(definitions[0], {
        __VUE_OPTIONS_API__: 'true',
        __VUE_PROD_DEVTOOLS__: 'false',
        __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: 'false'
      })
      return definitions
    })
  }
})
