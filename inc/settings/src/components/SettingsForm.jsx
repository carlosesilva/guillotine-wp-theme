import React, { useContext } from "react";
import { Formik, Form } from "formik";
import Section from "./Section";
import SettingField from "./SettingsField";
import SettingsContext from "../contexts/SettingsContext";

const SettingsForm = () => {
  const { schema, settings, saveSettings } = useContext(SettingsContext);

  const renderSettings = fields => {
    return Object.keys(fields).map(id => (
      <SettingField key={id} id={id} {...fields[id]} />
    ));
  };

  const renderSections = () => {
    return Object.keys(schema).map(section => (
      <Section key={section} name={section}>
        {renderSettings(schema[section])}
      </Section>
    ));
  };

  if (!settings) {
    // TODO: add better loading experience
    return "Loading...";
  }

  return (
    <Formik
      initialValues={settings}
      // TODO: Add validation
      onSubmit={async (values, { setSubmitting }) => {
        // TODO: add error handling when save fails
        await saveSettings(values);
        setSubmitting(false);
      }}
    >
      {({ isSubmitting }) => (
        <Form>
          {renderSections()}
          {/* TODO: Disable button if form does not pass validation */}
          {/* TODO: add some form of feedback for when save is done successfully */}
          <button type="submit" disabled={isSubmitting}>
            {isSubmitting ? "Please wait..." : "Save"}
          </button>
        </Form>
      )}
    </Formik>
  );
};
export default SettingsForm;
