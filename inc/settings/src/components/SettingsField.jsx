import React from "react";
import PropTypes from "prop-types";
import { Field, ErrorMessage } from "formik";

const SettingField = ({ id, name, type }) => {
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
        <ErrorMessage name={id} component="div" />
      </label>
    </li>
  );
};

SettingField.propTypes = {
  id: PropTypes.string.isRequired,
  name: PropTypes.string.isRequired,
  type: PropTypes.oneOf(["text", "email", "password", "url", "textarea"]),
};

SettingField.defaultProps = {
  type: "text",
};

export default SettingField;
