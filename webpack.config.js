const path = require('path');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const ManifestPlugin = require('webpack-manifest-plugin');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

module.exports = {
    entry: {
        app: ['./src/ts/bootstrap.ts', './ui/sass/app.scss'],
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
        new ManifestPlugin(),
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
                test: /\.(woff|woff2)$/,
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
    devtool: 'inline-source-map',
    node: {
        fs: 'empty',
    },
};
