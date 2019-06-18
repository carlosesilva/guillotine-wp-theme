import React from "react";
import PropTypes from "prop-types";

const Section = ({ name, children }) => {
  return (
    <section>
      <h2>{name}</h2>
      {/* TODO: Add option to add a section description with links to documentation if needed */}
      <ul>{children}</ul>
    </section>
  );
};

Section.propTypes = {
  name: PropTypes.string.isRequired,
  children: PropTypes.node.isRequired,
};

export default Section;
