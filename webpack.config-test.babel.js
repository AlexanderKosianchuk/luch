'use strict';

import nodeExternals from 'webpack-node-externals';
import webpack from 'webpack';
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
    devtool: "cheap-module-source-map",
    plugins: [
        new webpack.DefinePlugin({
            NODE_ENV: JSON.stringify('test'),
        })
    ],
};
