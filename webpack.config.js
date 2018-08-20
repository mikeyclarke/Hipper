const path = require('path');
const CleanWebpackPlugin = require('clean-webpack-plugin');
const ManifestPlugin = require('webpack-manifest-plugin');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const { styles: ckEditorStyles } = require( '@ckeditor/ckeditor5-dev-utils' );

module.exports = {
    entry: {
        app: './src/ts/bootstrap.ts',
    },
    output: {
        path: path.resolve(__dirname, 'public-roots/app/public/build'),
        publicPath: 'build/',
        filename: '[name].[contenthash].js',
    },
    plugins: [
        new CleanWebpackPlugin(
            [
                path.resolve(__dirname, 'public-roots/app/public/build'),
            ],
            {
                beforeEmit: true,
            }
        ),
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
                test: /ckeditor5-[^/]+\/theme\/icons\/[^/]+\.svg$/,
                use: ['raw-loader'],
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
            {
                // WARNING: Mikey’s shitty regex below, may cause performance issues 🤷
                test: /ckeditor5-[^/]+[\/a-zA-Z0-9]+\/[^/]+\.css$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    'css-loader',
                    {
                        loader: 'postcss-loader',
                        options: ckEditorStyles.getPostCssConfig({
                            themeImporter: {
                                themePath: require.resolve('@ckeditor/ckeditor5-theme-lark'),
                            },
                            minify: true,
                        }),
                    },
                ],
            },
        ],
    },
    node: {
        fs: 'empty',
    },
};
