'use strict';

const NODE_ENV = process.env.NODE_ENV || 'dev';
const webpack = require('webpack');
const path = require('path');
const WebpackCleanupPlugin = require('webpack-cleanup-plugin');

module.exports = {
    entry: {
        chart: './front/chart',
        index: './front/index',
        login: './front/login'
    },
    output: {
        path: path.join(__dirname, '/public'),
        publicPath: '/public/',
        filename: '[name][hash:6].js',
    },
    resolve: {
        modules: [
            path.resolve('./front'),
            path.resolve('./node_modules')
        ],
        extensions: ['.js', '.jsx'],
        alias: {
            'Language': path.join(__dirname, 'front/proto/Lang.proto.js'),
            'WindowFactory': path.join(__dirname, 'front/proto/WindowFactory.proto.js'),
            'Calibration': path.join(__dirname, 'front/proto/calibration/Calibration.proto.js'),
            'AxesWorker': path.join(__dirname, 'front/proto/chart/AxesWorker.proto.js'),
            'Chart': path.join(__dirname, 'front/proto/chart/Chart.proto.js'),
            'Coordinate': path.join(__dirname, 'front/proto/chart/Coordinate.proto.js'),
            'Exception': path.join(__dirname, 'front/proto/chart/Exception.proto.js'),
            'Legend': path.join(__dirname, 'front/proto/chart/Legend.proto.js'),
            'Param': path.join(__dirname, 'front/proto/chart/Param.proto.js'),
            'Fdr': path.join(__dirname, 'front/proto/fdr/Fdr.proto.js'),
            'FdrCyclo': path.join(__dirname, 'front/proto/fdr/FdrCyclo.proto.js'),
            'FdrEvents': path.join(__dirname, 'front/proto/fdr/FdrEvents.proto.js'),
            'FdrGeneralInfo': path.join(__dirname, 'front/proto/fdr/FdrGeneralInfo.proto.js'),
            'FdrTemplates': path.join(__dirname, 'front/proto/fdr/FdrTemplates.proto.js'),
            'FlightUploader': path.join(__dirname, 'front/proto/flight/FlightUploader.proto.js'),
            'FlightList': path.join(__dirname, 'front/proto/flight/FlightList.proto.js'),
            'SearchFlight': path.join(__dirname, 'front/proto/searchFlight/SearchFlight.proto.js'),
            'User': path.join(__dirname, 'front/proto/user/User.proto.js'),
            'FlightViewOptions': path.join(__dirname, 'front/proto/viewOptions/ViewOptions.proto.js'),
        },
    },
    watch: NODE_ENV == 'dev',
    watchOptions: {
        aggregateTimeout: 300,
    },
    devtool: NODE_ENV == 'dev' ? 'source-map' : null,
    module: {
        loaders: [
            {
                test: /\.(js|jsx)$/,
                loaders: 'babel-loader',
                exclude: /node_modules/,
                query: {
                  presets: ['es2015', 'react', 'stage-1']
                }
            }, {
                test: /\.css$/,
                use: [
                    'style-loader',
                    'css-loader'
                ],
            }, {
                test: /\.sass$/,
                exclude: /node_modules/,
                use: [
                    'style-loader', {
                        loader: 'css-loader',
                        query: {
                            sourceMaps: NODE_ENV == 'dev'
                        }
                    }, {
                        loader: 'sass-loader',
                        options: {
                            sourceMap: NODE_ENV == 'dev'
                        }
                    }
                ]
            }, {
                test: /\.(jpe?g|png|svg|gif)$/i,
                loader:'file-loader?name=images/[name].[ext]'
            }, {
                test: /\.(ttf|eot|woff|woff2)$/i,
                loader:'file-loader?name=fonts/[name].[ext]'
            }, {
                test: /bootstrap\/dist\/js\/umd\//,
                loader: 'imports-loader?jQuery=jquery'
            }
        ]
    },
    plugins: [
        new webpack.DefinePlugin({
            NODE_ENV: JSON.stringify(NODE_ENV),
            ENTRY_URL: JSON.stringify('/entry.php'),
        }),
        new webpack.ProvidePlugin({
            $: 'jquery',
            jQuery: 'jquery',
            'window.jQuery': 'jquery'
        }),
        new WebpackCleanupPlugin()
    ],

};
