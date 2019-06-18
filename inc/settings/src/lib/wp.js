import WPAPI from "wpapi";

// Get wpapi config and instantiate it.
if (!window.WPAPI_Config) {
  throw new Error("Missing WPAPI_Config variable in the window object.");
}
export default new WPAPI({
  endpoint: window.WPAPI_Config.endpoint,
  nonce: window.WPAPI_Config.nonce,
});
