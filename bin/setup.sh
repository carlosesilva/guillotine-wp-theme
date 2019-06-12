#!/bin/bash
set -e

local_setup_version="0.1"
current_local_setup_version_file=".current-local-setup-version"

# Runs any kind of setup needed to ensure all developers are in sync.
# This could include registering git hooks, etc...
setup() {
    echo "Setting up your local environment..."
    # Register git hooks
    git config core.hooksPath .githooks

    # save setup version to a file
    echo $local_setup_version > $current_local_setup_version_file

    echo "Setup complete! Your current local setup version is v$local_setup_version"
    echo ""
}

# Checks if the current local setup is up to date.
check() {
    if test -f "$current_local_setup_version_file"; then
        current_local_setup_version=`cat $current_local_setup_version_file`
        if [ "$current_local_setup_version" != "$local_setup_version" ]; then
            echo "WARNING: Your local setup is out of date!"
            echo "Please update your local setup by running the following:"
            echo "  $ npm run setup:update"
            echo ""
        fi
    else
        setup
    fi
}

# Updates local setup from previous versions.
update() {
    echo "Updating local setup..."
    # Upgrade scripts (happen in a cascade fashion, 0.1 => 0.2 => 0.3 => 0.4...)
    # if version 0.1, do xyz and update version to 0.2
    # if version 0.2 do srt and update version to 0.3
    # ...
    echo "Update complete!"
    echo ""
}

case "$1" in
    check)
        check
        ;;
    update)
        update
        ;;
    *)
        echo $"Usage: $0 <check|update>"
        exit 1
esac