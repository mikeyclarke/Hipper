module.exports = function(config) {
    config.set({
        frameworks: ["jasmine", "karma-typescript"],
        files: [
            "./test/*.test.ts",
            './assets/app/**/*.ts'
        ],
        preprocessors: {
            "**/*.ts": ["karma-typescript"]
        },
        // logLevel: config.LOG_DEBUG,
        singleRun: false,
        reporters: ["progress"],
        browsers: ["Chrome"]
    });
};
