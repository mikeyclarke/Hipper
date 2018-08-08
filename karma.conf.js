module.exports = function(config) {
    config.set({
        frameworks: ["jasmine", "karma-typescript"],
        files: [
            'node_modules/babel-polyfill/browser.js',
            './tests/ts/**/*.test.ts',
            './src/ts/Library/**/*.ts'
        ],
        preprocessors: {
            "**/*.ts": ["karma-typescript"]
        },
        // logLevel: config.LOG_DEBUG,
        singleRun: false,
        reporters: ["progress"],
        browsers: ["Chrome"],
        karmaTypescriptConfig: {
            bundlerOptions: {
                transforms: [
                    require("karma-typescript-es6-transform")()
                ]
            }
        }
    });
};
