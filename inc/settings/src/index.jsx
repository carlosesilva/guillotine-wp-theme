import React from "react";
import ReactDOM from "react-dom";
import SettingsForm from "./components/SettingsForm";
import { SettingsProvider } from "./contexts/SettingsContext";

const App = () => {
  return (
    <SettingsProvider>
      <SettingsForm />
    </SettingsProvider>
  );
};

const domContainer = document.querySelector("#guillotine-settings-root");
ReactDOM.render(<App />, domContainer);
