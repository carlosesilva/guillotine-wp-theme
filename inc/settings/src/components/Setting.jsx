import React from "react";
import PropTypes from "prop-types";

const Setting = ({ id, name, type, value, onChange }) => {
  const renderField = () => {
    switch (type) {
      case "textarea":
        return (
          <textarea name={id} id={id} onChange={onChange}>
            {value}
          </textarea>
        );

      default:
        return (
          <input
            type={type}
            name={id}
            id={id}
            value={value}
            onChange={onChange}
          />
        );
    }
  };

  return (
    <label htmlFor={id}>
      {name}
      {renderField()}
    </label>
  );
};

Setting.propTypes = {
  id: PropTypes.string.isRequired,
  name: PropTypes.string.isRequired,
  type: PropTypes.oneOf(["text", "textarea"]),
  value: PropTypes.string.isRequired,
  onChange: PropTypes.func.isRequired,
};

Setting.defaultProps = {
  type: "text",
};

export default Setting;
