const path = require('path');
const autoprefixer = require('autoprefixer');
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
    stats: {
        children: false
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
        modules: [
            path.resolve('./src/ts'),
            path.resolve('./node_modules'),
        ],
    },
    module: {
        rules: [
            {
                test: /\.ts$/,
                enforce: 'pre',
                use: [
                    {
                        loader: 'tslint-loader',
                        options: {
                            configFile: 'tslint.json'
                        }
                    }
                ]
            },
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
                    {
                        loader: 'postcss-loader',
                        options: {
                            ident: 'postcss',
                            plugins: [
                                autoprefixer,
                            ]
                        },
                    },
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
                // WARNING: Mikey’s shitty regex strikes again, this separates our SVGs from those of CKEditor
                test: /^(?!ckeditor5)[\_\-\/a-zA-Z0-9]+\.svg$/,
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
