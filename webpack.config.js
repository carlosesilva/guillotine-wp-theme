const path = require("path");

module.exports = {
  entry: {
    options: "./inc/options/src/index.jsx"
  },
  output: {
    filename: "[name].js",
    path: path.resolve(__dirname, "dist")
  }
};
