import React, { useState, useEffect } from "react";
import WPAPI from "wpapi";
import Section from "./Section";
import Setting from "./Setting";

const wp = new WPAPI({
  endpoint: window.WP_API_Settings.endpoint,
  nonce: window.WP_API_Settings.nonce,
});

const getGuillotineSettings = async () => {
  return wp.settings().then(res => {
    const guillotineSettings = {};
    Object.entries(res).forEach(([key, value]) => {
      if (key.startsWith("guillotine")) {
        guillotineSettings[key] = value;
      }
    });
    return guillotineSettings;
  });
};

const Settings = () => {
  const [settings, setSettings] = useState();

  useEffect(() => {
    async function fetchSettings() {
      try {
        const guillotineSettings = await getGuillotineSettings();
        setSettings(guillotineSettings);
      } catch (error) {
        console.error(error);
      }
    }
    fetchSettings();
  }, []);

  return (
    <Section>
      <Setting name="Frontend URL" id="frontend_url" />
      <pre>{JSON.stringify(settings, null, 2)}</pre>
    </Section>
  );
};
export default Settings;
