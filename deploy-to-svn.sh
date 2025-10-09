#!/bin/bash

################################################################################
# WordPress Plugin SVN Deployment Script
# For: URL Content Mapper
# Platform: macOS/Linux
################################################################################

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PLUGIN_SLUG="url-content-mapper"
PLUGIN_VERSION="1.3"
PLUGIN_DIR="/Users/wmubashir/Desktop/Plugin/url-content-mapper"
SVN_DIR="$HOME/svn-${PLUGIN_SLUG}"
SVN_URL="https://plugins.svn.wordpress.org/${PLUGIN_SLUG}"

# Files/folders to exclude (will not be copied to SVN)
EXCLUDE_PATTERNS=(
    ".git"
    ".github"
    ".claude"
    ".gitignore"
    ".DS_Store"
    "node_modules"
    "*.log"
    "*.sh"
    "DEPLOYMENT-GUIDE.md"
    "DOCS"
)

################################################################################
# Helper Functions
################################################################################

print_step() {
    echo -e "\n${BLUE}==>${NC} ${1}"
}

print_success() {
    echo -e "${GREEN}✓${NC} ${1}"
}

print_error() {
    echo -e "${RED}✗${NC} ${1}"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} ${1}"
}

check_svn_installed() {
    if ! command -v svn &> /dev/null; then
        print_error "SVN is not installed. Please install it first:"
        echo "  brew install subversion"
        exit 1
    fi
    print_success "SVN is installed"
}

check_plugin_dir() {
    if [ ! -d "$PLUGIN_DIR" ]; then
        print_error "Plugin directory not found: $PLUGIN_DIR"
        exit 1
    fi
    print_success "Plugin directory found"
}

################################################################################
# Main Deployment Steps
################################################################################

print_step "Starting WordPress.org Plugin Deployment"
echo "Plugin: ${PLUGIN_SLUG}"
echo "Version: ${PLUGIN_VERSION}"
echo ""

# Step 1: Check prerequisites
print_step "Step 1: Checking prerequisites"
check_svn_installed
check_plugin_dir

# Step 2: Checkout or update SVN repository
print_step "Step 2: Setting up SVN repository"

if [ -d "$SVN_DIR" ]; then
    print_warning "SVN directory already exists. Updating..."
    cd "$SVN_DIR"
    svn update
    print_success "SVN repository updated"
else
    print_warning "Checking out SVN repository (this may take a few minutes)..."
    svn checkout "$SVN_URL" "$SVN_DIR"
    cd "$SVN_DIR"
    print_success "SVN repository checked out"
fi

# Step 3: Clean trunk directory
print_step "Step 3: Cleaning trunk directory"
if [ -d "trunk" ]; then
    # Remove all files except .svn
    find trunk -mindepth 1 -maxdepth 1 ! -name '.svn' -exec rm -rf {} +
    print_success "Trunk directory cleaned"
else
    mkdir trunk
    print_success "Trunk directory created"
fi

# Step 4: Copy plugin files to trunk
print_step "Step 4: Copying plugin files to trunk"

# Build rsync exclude options
RSYNC_EXCLUDES=""
for pattern in "${EXCLUDE_PATTERNS[@]}"; do
    RSYNC_EXCLUDES="$RSYNC_EXCLUDES --exclude=$pattern"
done

# Copy files using rsync (better than cp for excluding patterns)
rsync -av $RSYNC_EXCLUDES "$PLUGIN_DIR/" "$SVN_DIR/trunk/"

print_success "Files copied to trunk"

# Step 5: Show SVN status
print_step "Step 5: Checking SVN status"
cd "$SVN_DIR/trunk"
svn status

# Count changes
ADDED=$(svn status | grep "^?" | wc -l | tr -d ' ')
MODIFIED=$(svn status | grep "^M" | wc -l | tr -d ' ')
DELETED=$(svn status | grep "^!" | wc -l | tr -d ' ')

echo ""
echo "Summary:"
echo "  - Modified files: $MODIFIED"
echo "  - New files: $ADDED"
echo "  - Deleted files: $DELETED"

# Step 6: Ask for confirmation before proceeding
echo ""
print_warning "IMPORTANT: Please review the changes above."
echo ""
echo "This script will:"
echo "  1. Add all new files to SVN"
echo "  2. Remove deleted files from SVN"
echo "  3. Commit changes to trunk"
echo "  4. Create version tag: $PLUGIN_VERSION"
echo "  5. Commit the tag"
echo ""
read -p "Do you want to proceed? (yes/no): " -r
echo ""

if [[ ! $REPLY =~ ^[Yy][Ee][Ss]$ ]]; then
    print_warning "Deployment cancelled by user"
    exit 0
fi

# Step 7: Add new files
if [ "$ADDED" -gt 0 ]; then
    print_step "Step 7: Adding new files to SVN"
    svn status | grep "^?" | awk '{print $2}' | xargs svn add
    print_success "New files added"
else
    print_step "Step 7: No new files to add"
fi

# Step 8: Remove deleted files
if [ "$DELETED" -gt 0 ]; then
    print_step "Step 8: Removing deleted files from SVN"
    svn status | grep "^!" | awk '{print $2}' | xargs svn delete
    print_success "Deleted files removed"
else
    print_step "Step 8: No deleted files to remove"
fi

# Step 9: Commit to trunk
print_step "Step 9: Committing changes to trunk"
svn commit -m "Update to version $PLUGIN_VERSION - Improved query parameter validation and GA4 timing fixes"

print_success "Changes committed to trunk"

# Step 10: Create and commit tag
print_step "Step 10: Creating version tag"

cd "$SVN_DIR"

if [ -d "tags/$PLUGIN_VERSION" ]; then
    print_warning "Tag $PLUGIN_VERSION already exists. Skipping tag creation."
else
    svn copy trunk "tags/$PLUGIN_VERSION"
    svn commit -m "Tagging version $PLUGIN_VERSION"
    print_success "Version $PLUGIN_VERSION tagged and committed"
fi

# Final success message
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
print_success "Deployment completed successfully!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "Your plugin has been deployed to WordPress.org"
echo "Version: $PLUGIN_VERSION"
echo ""
echo "It may take a few minutes for the changes to appear on wordpress.org"
echo "Plugin URL: https://wordpress.org/plugins/$PLUGIN_SLUG/"
echo ""
