import vue from 'eslint-plugin-vue';
import vueParser from 'vue-eslint-parser';
import prettier from 'eslint-config-prettier';

export default [
    {
        files: ['resources/js/**/*.{js,vue}'],
        languageOptions: {
            ecmaVersion: 2021,
            sourceType: 'module',
            parser: vueParser,
            parserOptions: {
                ecmaVersion: 2021,
                sourceType: 'module',
            },
        },
        plugins: {
            vue: vue,
        },
        rules: {
            'vue/multi-word-component-names': 'off',
        },
    },
    ...vue.configs['flat/recommended'], // Configuration correcte pour Vue 3
    prettier,
];
