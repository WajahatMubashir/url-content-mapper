document.addEventListener("DOMContentLoaded", function () {
    const categoriesContainer = document.getElementById("urlcoma-categories");
    const addCategoryBtn = document.getElementById("urlcoma-addCategory");

    if (!addCategoryBtn || !categoriesContainer) {
        return;
    }

    function generateUniqueId() {
        return "cat_" + Date.now();
    }

    function createCategoryElement(categoryId) {
        let div = document.createElement("div");
        div.classList.add("urlcoma-category-container");
        div.setAttribute("data-category-id", categoryId);
        div.innerHTML = `
            <input type="hidden" name="urlcoma_mapper_data[${categoryId}][category_id]" value="${categoryId}" />

            <label>Category Name:</label>
            <input type="text" name="urlcoma_mapper_data[${categoryId}][name]" required>

            <label>Match Type:</label>
            <select name="urlcoma_mapper_data[${categoryId}][type]">
                <option value="exact">Exact</option>
                <option value="contains">Contains</option>
            </select>

            <label>URLs:</label>
            <div class="urlcoma-url-list"></div>

            <button type="button" class="urlcoma-add-url">Add URL</button>
            <button type="button" class="urlcoma-remove-category">Remove Category</button>
        `;
        return div;
    }

    function updateCategoryIndexes() {
        let categories = categoriesContainer.querySelectorAll(".urlcoma-category-container");
        categories.forEach((category, index) => {
            let categoryId = "cat_" + index;
            category.setAttribute("data-category-id", categoryId);
            let hiddenInput = category.querySelector("input[type='hidden']");
            if (hiddenInput) {
                hiddenInput.value = categoryId;
                hiddenInput.name = `urlcoma_mapper_data[${categoryId}][category_id]`;
            }

            let nameInput = category.querySelector("input[type='text']");
            if (nameInput) {
                nameInput.name = `urlcoma_mapper_data[${categoryId}][name]`;
            }

            let matchType = category.querySelector("select");
            if (matchType) {
                matchType.name = `urlcoma_mapper_data[${categoryId}][type]`;
            }

            let urlList = category.querySelector(".urlcoma-url-list");
            if (urlList) {
                let urlInputs = urlList.querySelectorAll("input[type='text']");
                urlInputs.forEach((urlInput, urlIndex) => {
                    urlInput.name = `urlcoma_mapper_data[${categoryId}][urls][${urlIndex}]`;
                });
            }
        });
    }

    addCategoryBtn.addEventListener("click", function () {
        let categoryId = generateUniqueId();
        let categoryElement = createCategoryElement(categoryId);
        categoriesContainer.appendChild(categoryElement);
        updateCategoryIndexes();
    });

    categoriesContainer.addEventListener("click", function (event) {
        let categoryDiv = event.target.closest(".urlcoma-category-container");

        if (event.target.classList.contains("urlcoma-add-url")) {
            let categoryId = categoryDiv.getAttribute("data-category-id");
            let urlList = categoryDiv.querySelector(".urlcoma-url-list");
            let urlIndex = urlList.children.length;

            let urlDiv = document.createElement("div");
            urlDiv.classList.add("urlcoma-url-item");
            urlDiv.innerHTML = `
                <input type="text" name="urlcoma_mapper_data[${categoryId}][urls][${urlIndex}]" required>
                <button type="button" class="urlcoma-remove-url">Remove</button>
            `;
            urlList.appendChild(urlDiv);
        }

        if (event.target.classList.contains("urlcoma-remove-url")) {
            event.target.parentNode.remove();
        }

        if (event.target.classList.contains("urlcoma-remove-category")) {
            categoryDiv.remove();
            updateCategoryIndexes();
        }
    });

    // Import/Export functionality
    const importFileInput = document.getElementById("urlcoma_import_file");
    const importForm = importFileInput ? importFileInput.closest("form") : null;

    if (importFileInput) {
        // File validation
        importFileInput.addEventListener("change", function() {
            const file = this.files[0];
            if (!file) return;

            // Check file type
            if (!file.name.toLowerCase().endsWith('.json')) {
                alert('Please select a JSON file.');
                this.value = '';
                return;
            }

            // Check file size (1MB limit)
            if (file.size > 1048576) {
                alert('File size is too large. Maximum size is 1MB.');
                this.value = '';
                return;
            }

            // Optional: Basic JSON validation by trying to read the file
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const jsonData = JSON.parse(e.target.result);
                    
                    // Basic validation
                    if (!jsonData.plugin || jsonData.plugin !== 'url-content-mapper') {
                        alert('This file does not appear to be a valid URL Content Mapper export.');
                        importFileInput.value = '';
                        return;
                    }

                    // Show file info
                    let fileInfo = 'File loaded successfully.\n';
                    if (jsonData.export_date) {
                        fileInfo += 'Export date: ' + new Date(jsonData.export_date).toLocaleString() + '\n';
                    }
                    if (jsonData.version) {
                        fileInfo += 'Plugin version: ' + jsonData.version + '\n';
                    }
                    if (jsonData.settings && jsonData.settings.urlcoma_mapper_data) {
                        const categoryCount = Object.keys(jsonData.settings.urlcoma_mapper_data).length;
                        fileInfo += 'Categories: ' + categoryCount;
                    }

                    // Display file info in a non-intrusive way
                    let fileInfoElement = document.getElementById('urlcoma-file-info');
                    if (!fileInfoElement) {
                        fileInfoElement = document.createElement('div');
                        fileInfoElement.id = 'urlcoma-file-info';
                        fileInfoElement.className = 'notice notice-info inline';
                        importFileInput.parentNode.appendChild(fileInfoElement);
                    }
                    fileInfoElement.innerHTML = '<p>' + fileInfo.replace(/\n/g, '<br>') + '</p>';

                } catch (error) {
                    alert('Invalid JSON file format.');
                    importFileInput.value = '';
                }
            };
            reader.readAsText(file);
        });
    }

    // Export button enhancement
    const exportButton = document.querySelector('input[name="urlcoma_export"]');
    if (exportButton) {
        exportButton.addEventListener('click', function(e) {
            // Check if there's data to export
            const categories = document.querySelectorAll('.urlcoma-category-container');
            if (categories.length === 0) {
                if (!confirm('No categories are currently configured. Do you still want to export an empty configuration?')) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    }
});
