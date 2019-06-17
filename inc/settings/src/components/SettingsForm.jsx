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
    return "loading";
  }

  return (
    <Formik
      initialValues={settings}
      onSubmit={async (values, { setSubmitting }) => {
        await saveSettings(values);
        setSubmitting(false);
      }}
    >
      {props => (
        <Form>
          {renderSections()}
          <button type="submit" disabled={props.isSubmitting}>
            {props.isSubmitting ? "Please wait..." : "Save"}
          </button>
        </Form>
      )}
    </Formik>
  );
};
export default SettingsForm;
