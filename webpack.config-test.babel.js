'use strict';

import nodeExternals from 'webpack-node-externals';
import path from 'path';
import { resolve } from 'path';

export default {
    target: 'node',
    externals: [nodeExternals()],
    output: {
        devtoolModuleFilenameTemplate: '[absolute-resource-path]',
        devtoolFallbackModuleFilenameTemplate: '[absolute-resource-path]?[hash]'
    },
    resolve: {
        modules: [
            path.resolve('./front'),
            path.resolve('./node_modules')
        ],
    },
    module: {
        loaders: [{
            test: /\.js$/,
            loader: 'babel-loader',
        }, {
            test: /\.(s)?(c|a)ss$/,
            loader: 'null-loader'
        }]
    },
    devtool: "cheap-module-source-map"
};
