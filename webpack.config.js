const path = require('path');
const ManifestPlugin = require('webpack-manifest-plugin');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const { styles: ckEditorStyles } = require( '@ckeditor/ckeditor5-dev-utils' );

module.exports = {
    entry: {
        app: './js-src/bootstrap.ts',
    },
    output: {
        path: path.resolve(__dirname, 'public/build'),
        publicPath: 'build/',
        filename: '[name].[contenthash].js',
    },
    plugins: [
        new ManifestPlugin(),
        new MiniCssExtractPlugin({
            filename: '[name].[contenthash].css',
        }),
    ],
    resolve: {
        extensions: ['.ts', '.js', '.scss'],
    },
    module: {
        rules: [
            {
                test: /\.ts$/,
                use: [
                    {
                        loader: 'ts-loader',
                        options: {
                            transpileOnly: true
                        },
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
                test: /ckeditor5-[^/]+\/theme\/icons\/[^/]+\.svg$/,
                use: ['raw-loader'],
            },
            {
                // WARNING: Mikey’s shitty regex below, may cause performance issues 🤷
                test: /ckeditor5-[^/]+[\/a-zA-Z0-9]+\/[^/]+\.css$/,
                use: [
                    {
                        loader: 'style-loader',
                        options: {
                            singleton: true,
                        },
                    },
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
};
