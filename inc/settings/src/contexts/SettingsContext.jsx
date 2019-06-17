import React, { createContext, useState, useEffect } from "react";
import PropTypes from "prop-types";
import wp from "../lib/wp";

// Get settings schema.
if (!window.Guillotine_Settings_Schema) {
  throw new Error(
    "Missing Guillotine_Settings_Schema variable in the window object.",
  );
}
const guillotineSettingsSchema = window.Guillotine_Settings_Schema;

const SettingsContext = createContext({});

export const SettingsProvider = ({ children }) => {
  const [guillotineSettings, setGuillotineSettings] = useState();

  const getAllGuillotineSettingsDefinitions = () => {
    const allGuillotineSettingsDefinitions = {};
    Object.values(guillotineSettingsSchema).forEach(items => {
      Object.assign(allGuillotineSettingsDefinitions, items);
    }, {});
    return allGuillotineSettingsDefinitions;
  };

  const filterGuillotineSettings = wpSettings => {
    const allGuillotineSettingsDefinitions = getAllGuillotineSettingsDefinitions();
    const filteredSettings = {};
    Object.keys(wpSettings).forEach(id => {
      if (
        Object.prototype.hasOwnProperty.call(
          allGuillotineSettingsDefinitions,
          id,
        )
      ) {
        filteredSettings[id] = wpSettings[id];
      }
    });
    return filteredSettings;
  };

  const fetchSettings = async () => {
    try {
      const wpSettings = await wp.settings().then(res => res);
      const filteredGuillotineSettings = filterGuillotineSettings(wpSettings);
      setGuillotineSettings(filteredGuillotineSettings);
    } catch (error) {
      console.error("Unable to fetch guillotine settings. See error below:");
      console.error(error);
    }
  };

  const saveSettings = async newSettings => {
    try {
      const updatedSettings = await wp.settings().update(newSettings);
      setGuillotineSettings(updatedSettings);
    } catch (error) {
      console.error("Unable to save guillotine settings. See error below:");
      console.error(error);
    }
  };

  useEffect(() => {
    fetchSettings();
  }, []);

  return (
    <SettingsContext.Provider
      value={{
        schema: guillotineSettingsSchema,
        settings: guillotineSettings,
        fetchSettings,
        saveSettings,
      }}
    >
      {children}
    </SettingsContext.Provider>
  );
};

SettingsProvider.propTypes = {
  children: PropTypes.node.isRequired,
};

export default SettingsContext;
