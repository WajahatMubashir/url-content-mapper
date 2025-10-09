#!/bin/bash

################################################################################
# WordPress Plugin File Verification Script
# Verifies all files are ready for deployment
################################################################################

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

PLUGIN_DIR="/Users/wmubashir/Desktop/Plugin/url-content-mapper"
VERSION="1.3"

echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}  WordPress Plugin Verification${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

cd "$PLUGIN_DIR"

# Check 1: Required files exist
echo -e "${BLUE}1. Checking required files...${NC}"
REQUIRED_FILES=(
    "wp-url-content-mapper.php"
    "readme.txt"
    "functions.php"
    "admin-settings.php"
    "uninstall.php"
    "assets/admin-script.js"
    "assets/admin-style.css"
    "assets/frontend-script.js"
)

ALL_EXIST=true
for file in "${REQUIRED_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo -e "  ${GREEN}✓${NC} $file"
    else
        echo -e "  ${RED}✗${NC} $file (MISSING)"
        ALL_EXIST=false
    fi
done

if [ "$ALL_EXIST" = true ]; then
    echo -e "${GREEN}✓ All required files exist${NC}\n"
else
    echo -e "${RED}✗ Some required files are missing!${NC}\n"
    exit 1
fi

# Check 2: Version numbers
echo -e "${BLUE}2. Checking version numbers...${NC}"

# Check main plugin file
MAIN_VERSION=$(grep "Version:" wp-url-content-mapper.php | head -1 | sed 's/.*Version: *//' | tr -d ' ')
if [ "$MAIN_VERSION" = "$VERSION" ]; then
    echo -e "  ${GREEN}✓${NC} wp-url-content-mapper.php: $MAIN_VERSION"
else
    echo -e "  ${RED}✗${NC} wp-url-content-mapper.php: $MAIN_VERSION (expected $VERSION)"
fi

# Check readme.txt
README_VERSION=$(grep "Stable tag:" readme.txt | sed 's/.*Stable tag: *//' | tr -d ' ')
if [ "$README_VERSION" = "$VERSION" ]; then
    echo -e "  ${GREEN}✓${NC} readme.txt: $README_VERSION"
else
    echo -e "  ${RED}✗${NC} readme.txt: $README_VERSION (expected $VERSION)"
fi

echo ""

# Check 3: PHP syntax
echo -e "${BLUE}3. Checking PHP syntax...${NC}"
PHP_FILES=$(find . -name "*.php" ! -path "./.git/*" ! -path "./.claude/*")

SYNTAX_OK=true
for file in $PHP_FILES; do
    if php -l "$file" > /dev/null 2>&1; then
        echo -e "  ${GREEN}✓${NC} $file"
    else
        echo -e "  ${RED}✗${NC} $file (SYNTAX ERROR)"
        SYNTAX_OK=false
    fi
done

if [ "$SYNTAX_OK" = true ]; then
    echo -e "${GREEN}✓ All PHP files have valid syntax${NC}\n"
else
    echo -e "${RED}✗ Some PHP files have syntax errors!${NC}\n"
    exit 1
fi

# Check 4: Files that will be excluded
echo -e "${BLUE}4. Files that will be excluded from deployment:${NC}"
EXCLUDE_PATTERNS=(".git" ".claude" ".DS_Store" "*.log" "deploy-to-svn.sh" "verify-files.sh" "DOCS")

for pattern in "${EXCLUDE_PATTERNS[@]}"; do
    COUNT=$(find . -name "$pattern" ! -path "./.git/*" 2>/dev/null | wc -l | tr -d ' ')
    if [ "$COUNT" -gt 0 ]; then
        echo -e "  ${YELLOW}•${NC} $pattern (found $COUNT)"
    fi
done
echo ""

# Check 5: Files that will be deployed
echo -e "${BLUE}5. Files that will be deployed:${NC}"
find . -type f \
    ! -path "./.git/*" \
    ! -path "./.claude/*" \
    ! -path "./DOCS/*" \
    ! -name ".DS_Store" \
    ! -name "*.log" \
    ! -name "deploy-to-svn.sh" \
    ! -name "verify-files.sh" \
    | sort | while read file; do
    echo -e "  ${GREEN}•${NC} $file"
done

echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}✓ Verification complete!${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo "Ready to deploy? Run: ./deploy-to-svn.sh"
echo ""
