import React from "react";
import PropTypes from "prop-types";
import { Field, ErrorMessage } from "formik";

const SettingField = ({ id, name, type, description }) => {
  const renderField = () => {
    switch (type) {
      case "textarea":
        return <Field component="textarea" name={id} />;

      default:
        return <Field component="input" type={type} name={id} />;
    }
  };

  return (
    <li>
      <label htmlFor={id}>
        {`${name}: `}
        {renderField()}
      </label>
      <ErrorMessage name={id} component="div" />
      <div className="description">{description}</div>
    </li>
  );
};

SettingField.propTypes = {
  id: PropTypes.string.isRequired,
  name: PropTypes.string.isRequired,
  // TODO: add support for other setting types such as select, checkbox, radio
  type: PropTypes.oneOf(["text", "email", "password", "url", "textarea"]),
  description: PropTypes.string,
};

SettingField.defaultProps = {
  type: "text",
  description: null,
};

export default SettingField;
