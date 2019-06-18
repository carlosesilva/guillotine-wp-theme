const prod = process.env.NODE_ENV === "production";

module.exports = {
  root: true,
  env: {
    browser: true,
    es6: true,
    node: true,
  },
  extends: [
    "airbnb",
    "plugin:jest/recommended",
    // Remove rules that conflict with prettier.
    "prettier",
  ],
  globals: {
    Atomics: "readonly",
    fuse: "readonly",
  },
  parserOptions: {
    ecmaFeatures: {
      jsx: true,
    },
    ecmaVersion: 2018,
    sourceType: "module",
  },
  plugins: ["react", "jest"],
  rules: {
    "jsx-a11y/label-has-for": "off",
    "no-console": ["error", { allow: ["warn", "error"] }],
    "no-debugger": prod ? "error" : "warn",
    "import/no-extraneous-dependencies": [
      "error",
      {
        devDependencies: ["**/*.test.js", "**/*.test.jsx", "enzyme.config.js"],
      },
    ],
  },
};
