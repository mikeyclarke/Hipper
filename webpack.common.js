const path = require('path');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const WebpackAssetsManifest = require('webpack-assets-manifest');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

module.exports = {
    entry: {
        app: ['./src/ts/bootstrap/app.ts', './ui/sass/app.scss'],
        signup: ['./src/ts/bootstrap/signup.ts', './ui/sass/signup.scss'],
    },
    output: {
        path: path.resolve(__dirname, 'public-roots/assets/public/build'),
        publicPath: 'https://assets.usehipper.test/build/',
        filename: '[name].[contenthash].js',
        chunkFilename: '[name].[contenthash].js',
        crossOriginLoading: 'anonymous',
    },
    stats: {
        children: false,
        maxModules: 0,
    },
    plugins: [
        new CleanWebpackPlugin(),
        new WebpackAssetsManifest({
            publicPath: true,
        }),
        new WebpackAssetsManifest({
            customize(entry, original, manifest, asset) {
                return {
                    key: entry.key,
                    value: asset.integrity,
                };
            },
            integrity: true,
            integrityHashes: ['sha512'],
            output: 'integrity.json',
        }),
        new MiniCssExtractPlugin({
            filename: '[name].[contenthash].css',
        }),
    ],
    resolve: {
        extensions: ['.ts', '.js', '.scss'],
        alias: {
            Sass: path.resolve(__dirname, 'ui/sass'),
            Twig: path.resolve(__dirname, 'ui/twig'),
        },
        modules: [
            path.resolve('./src/ts'),
            path.resolve('./node_modules'),
        ],
    },
    module: {
        rules: [
            {
                test: /\.ts$/,
                use: [
                    {
                        loader: 'ts-loader',
                    },
                ],
            },
            {
                test: /\.scss$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    'css-loader',
                    'sass-loader',
                    {
                        loader: 'sass-loader',
                        options: {
                            webpackImporter: false,
                        },
                    },
                ],
            },
            {
                test: /\.twig$/,
                loader: 'twig-loader',
            },
            {
                test: /\.svg$/,
                use: {
                    loader: 'url-loader',
                    options: {
                        // Limit at 50k. Above that it emits separate files
                        limit: 50000,
                    },
                },
            },
            {
                test: /\.(woff2)$/,
                use: {
                    loader: 'url-loader',
                    options: {
                        // Limit at 50k. Above that it emits separate files
                        limit: 50000,
                    },
                },
            },
        ],
    },
    optimization: {
        splitChunks: false,
    },
    node: {
        fs: 'empty',
    },
};