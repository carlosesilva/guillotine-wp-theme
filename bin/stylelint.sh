#!/bin/bash
# Run all of the style lints

# Custom echo command.
msg() {
    echo "[stylelint.sh] $1"
}

# Get file count for a specific file extension
file_count() {
    ext=$1
    find . -type f -iname "*$ext" ! -path "./node_modules/*" ! -path "./dist/*" ! -path "./.next/*" ! -path "./styleguide/*"| wc -l
}

# Set script status to passing
status=0

# SCSS
scss_count=$(file_count ".scss")
if  [ $scss_count != 0 ]; then
    msg "Linting .scss files..."
    stylelint '**/*.scss'
    [[ "$?" -eq "0" ]] && msg "No linting issues found in .scss files." || status=1
fi 

# CSS
css_count=$(file_count ".css")
if  [ $css_count != 0 ]; then
    msg "Linting .css files..."
    stylelint '**/*.css'
    [[ "$?" -eq "0" ]] && msg "No linting issues found in .css files." || status=1
fi 

# HTML
html_count=$(file_count ".html")
if  [ $html_count != 0 ]; then
    msg "Linting .html files..."
    stylelint '**/*.html'
    [[ "$?" -eq "0" ]] && msg "No linting issues found in .html files." || status=1
fi 

exit $status