(function($) {
    $(document).ready(function() {
        const jsPDF = window.jspdf.jsPDF;
        new Vue({
            el: '#masterstudy-certificate-builder',
            components: {
                VueDraggableResizable,
                'photoshop-picker': VueColor.Photoshop
            },
            data() {
                return {
                    isAdmin: masterstudy_certificate_data.is_admin,
                    certificates: [],
                    currentCertificate: 0,
                    generatedPreviews: masterstudy_certificate_data.not_generated_previews,
                    defaultCertificate: '',
                    categoryCertificate: '',
                    newDefaultCertificate: '',
                    newCategoryCertificate: '',
                    certificateSearchQuery: '',
                    categoryToSave: '',
                    titleActive: false,
                    activeField: null,
                    activeControlsTab: 'elements',
                    openControlsSidebar: false,
                    createPopupVisible: false,
                    deletePopupVisible: false,
                    imagePopupVisible: false,
                    certificateForDelete: '',
                    categoryForDelete: '',
                    deleteAlertType: 'certificate',
                    notImageError: false,
                    notImageErrorField: false,
                    certificatesPopupVisible: false,
                    categoriesPopupVisible: false,
                    fields: [],
                    fieldsCategories: ['certificate','course','student','instructor'],
                    categories: [],
                    totalCategories: 0,
                    categoriesLimit: 10,
                    searchCategories: '',
                    searchCategoriesActive: false,
                    currentCategoriesPage: 1,
                    loadingCategoriesPage: false,
                    loadingSaveButton: false,
                    previewLoading: false,
                    certificateSaved: false,
                    colorPickerVisible: false,
                    currentTab: 'builder',
                    currentDestinationTab: 'default',
                    selectDropdownOpen: false,
                    selectedInstructorName: '',
                    selectedInstructorId: null,
                    dropdownShapeOpen: false,
                    shapes: masterstudy_certificate_data.shapes,
                    certificate_page: masterstudy_certificate_data.certificate_page,
                    qrImage: masterstudy_certificate_data.qr_image_url,
                    ActiveShapefield: null,
                    ActiveShapefieldKey: null,
                    activeFieldShapeElement: null,
                    refreshKey: 0,
                }
            },
            mounted() {
                Promise.all([this.getAllCertificates(), this.getFields()]).then(() => {
                    const loader = document.querySelector('.masterstudy-loader_global');
                    if (loader) {
                        loader.style.display = 'none';
                    }
                    this.goToTab();
                    this.goToCertificate();
                })
                document.addEventListener('click', this.handleClickOutside);
                document.addEventListener('click', this.handleClickOutsideShapeSettings);
                if (this.isAdmin) {
                    this.getCategories();
                }
            },
            computed: {
                savedCertificates() {
                    return this.certificates.filter(certificate => certificate.id);
                },
                filteredCertificates() {
                    if (this.categoriesPopupVisible) {
                        return this.savedCertificates.filter(certificate => 
                          certificate?.data?.category.length < 1
                        );
                      } else {
                        return this.savedCertificates;
                      }
                },
                certificatesList() {
                    const filteredByInstructor = this.selectedInstructorId 
                    ? this.certificates.filter(certificate => certificate.author && certificate.author.id === this.selectedInstructorId)
                    : this.certificates;

                    return filteredByInstructor.filter(certificate => {
                        const title = certificate.title ? certificate.title.toLowerCase() : '';
                        const id = certificate.id ? certificate.id.toString() : '';
                        const searchQueryLower = this.certificateSearchQuery.toLowerCase();

                        return title.includes(searchQueryLower) || id.includes(this.certificateSearchQuery);
                    });
                },
                categoriesWithCertificates() {
                    return this.categories.map(category => {
                        const certificate = this.certificates.find(certificate => {
                            const categoriesArray = certificate.data?.category.split(',') || [];
                            return categoriesArray.includes(String(category.id));
                        });
                        return {
                            ...category,
                            certificate: certificate || null,
                        };
                    });
                },
                totalCategoriesPages() {
                    if (this.totalCategories > 0) {
                        return Math.ceil(this.totalCategories / this.categoriesLimit);
                    }
                },
                categoriesPagesToShow() {
                    const maxPages = 5;
                    const middlePage = Math.floor(maxPages / 2);
                    let startPage = Math.max(this.currentCategoriesPage - middlePage, 1);
                    let endPage = Math.min(startPage + maxPages - 1, this.totalCategoriesPages);

                    if (endPage - startPage < maxPages - 1) {
                        startPage = Math.max(endPage - maxPages + 1, 1);
                    }

                    const pages = [];
                    for (let i = startPage; i <= endPage; i++) {
                        pages.push(i);
                    }

                    return pages;
                },
                categoriesShowPrev() {
                    return this.currentCategoriesPage > 1;
                },
                categoriesShowNext() {
                    return this.currentCategoriesPage < this.totalCategoriesPages;
                },
                uniqueInstructors() {
                    const instructorsMap = new Map();
                    this.certificates.forEach(certificate => {
                        if (certificate.author && !instructorsMap.has(certificate.author.id)) {
                            instructorsMap.set(certificate.author.id, certificate.author.name);
                        }
                    });
                    return Array.from(instructorsMap, ([id, name]) => ({ id, name }));
                },
            },
            watch: {
                currentCertificate(newValue, oldValue) {
                    this.addActualTitle();
                },
            },
            methods: {
                handleClickOutside(event) {
                    if (this.$refs.titleContainer && !this.$refs.titleContainer.contains(event.target)) {
                      this.titleActive = false;
                    }
                    const vcPhotoshopContainer = document.querySelector('.vc-photoshop');
                    if ( vcPhotoshopContainer ) {
                        if (!document.querySelector('.vc-photoshop').contains(event.target) && !this.isEventInsideColor(event.target)) {
                            this.colorPickerVisible = false;
                        }
                    }
                },
                isEventInsideColor(eventTarget) {
                    let node = eventTarget;
                    while (node != null) {
                        if (node.matches && node.matches('.font-style .color')) {
                            return true;
                        }
                        node = node.parentNode;
                    }
                    return false;
                },
                toggleSelectDropdown() {
                    this.selectDropdownOpen = !this.selectDropdownOpen;
                },
                selectInstructor(instructor) {
                    if (instructor) {
                        this.selectedInstructorId = instructor.id;
                        this.selectedInstructorName = instructor.name;
                    } else {
                        this.selectedInstructorId = null;
                        this.selectedInstructorName = '';
                    }
                    this.selectDropdownOpen = false;
                },
                goToTab() {
                    const url = new URL(window.location);
                    const tab = url.searchParams.get('tab');
                    const destination = url.searchParams.get('destination');
                    if (tab) {
                        this.currentTab = tab;
                    }
                    if (destination) {
                        this.currentDestinationTab = destination;
                    }
                },
                goToCertificate() {
                    const url = new URL(window.location);
                    const current = url.searchParams.get('certificate_id');
                    if (current) {
                        const index = this.certificates.findIndex(certificate => certificate.id === parseInt(current));
                        if (index !== -1) {
                            this.currentCertificate = index;
                        }
                        this.$nextTick(() => {
                            const contentContainer = document.querySelector('.masterstudy-certificate-templates__content');
                            const certificateElement = document.querySelector(`.masterstudy-certificate-templates__item[data-id="${current}"]`);
                            if (certificateElement && contentContainer) {
                                const elementTop = certificateElement.offsetTop;
                                contentContainer.scrollTop = elementTop - 155;
                            }
                        });
                    }
                },
                changeCurrentTab(tab, openPopup = false) {
                    this.currentTab = tab;
                    this.addActualTitle();
                    const url = new URL(window.location);
                    url.searchParams.set('tab', tab);
                    window.history.pushState({ path: url.toString() }, '', url.toString());
                    if (openPopup) {
                        const _this = this;
                        setTimeout(function() {
                            _this.openCreatePopup();
                        }, 200);
                    }
                },
                changeCurrentDestinationTab(tab) {
                    this.currentDestinationTab = tab;
                    const url = new URL(window.location);
                    url.searchParams.set('destination', tab);
                    window.history.pushState({ path: url.toString() }, '', url.toString());
                },
                addActualTitle() {
                    const currentTitle = this.certificates[this.currentCertificate]?.title;
                    this.$nextTick(() => {
                        if (this.$refs.editCertificateTitle && currentTitle !== undefined) {
                            this.$refs.editCertificateTitle.textContent = currentTitle;
                        }
                    });
                },
                colorPickerShow() {
                    this.colorPickerVisible = true;
                },
                getAllCertificates() {
                    const url = stm_lms_ajaxurl + '?action=stm_get_certificates&nonce=' + stm_lms_nonces['stm_get_certificates'];
                    return this.$http.get(url).then((response) => {
                        this.$set(this, 'certificates', response.body);
                        if (!this.generatedPreviews.length > 0) {
                            this.previewGeneration();
                        }
                        this.addActualTitle();
                        this.getDefaultCertificate();
                    });
                },
                previewCertificate(event) {
                    this.previewLoading = true;
                    const id = event.currentTarget.getAttribute('data-id');
                    if (id) {
                        this.getCertificate(id);
                    }
                },
                getCertificate(id) {
                    const url = stm_lms_ajaxurl + '?action=stm_get_certificate&nonce=' + stm_lms_nonces['stm_get_certificate'] + '&post_id=' + id;
                    this.$http.get(url).then((response) => {
                        if (typeof response.body.data !== 'undefined') {
                            this.generateCertificate(response.body.data, true);
                        }
                    });
                },
                generateCertificate(certificate, preview) {
                    return new Promise((resolve, reject) => {
                        this.$nextTick(() => {
                            let orientation = preview ? certificate.orientation : certificate.data.orientation;
                            let certificate_fields = preview ? certificate.fields : certificate.data.fields;

                            let doc = new jsPDF({
                                orientation: orientation,
                                unit: 'px',
                                format: [600, 900],
                            });

                            const background = certificate.thumbnail;
                            const _this = this;

                            doc.addFileToVFS('OpenSans-Regular-normal.ttf', openSansRegular);
                            doc.addFont('OpenSans-Regular-normal.ttf', 'OpenSans', 'normal');
                            doc.addFileToVFS('OpenSans-Bold-normal.ttf', openSansBold);
                            doc.addFont('OpenSans-Bold-normal.ttf', 'OpenSans', 'bold');
                            doc.addFileToVFS('OpenSans-BoldItalic-normal.ttf', openSansBoldItalic);
                            doc.addFont('OpenSans-BoldItalic-normal.ttf', 'OpenSans', 'bolditalic');
                            doc.addFileToVFS('OpenSans-Italic-italic.ttf', openSansItalic);
                            doc.addFont('OpenSans-Italic-italic.ttf', 'OpenSans', 'italic');
                            doc.addFileToVFS('Montserrat-normal.ttf', montserratRegular);
                            doc.addFont('Montserrat-normal.ttf', 'Montserrat', 'normal');
                            doc.addFileToVFS('Montserrat-bold.ttf', montserratBold);
                            doc.addFont('Montserrat-bold.ttf', 'Montserrat', 'bold');
                            doc.addFileToVFS('Montserrat-italic.ttf', montserratItalic);
                            doc.addFont('Montserrat-italic.ttf', 'Montserrat', 'italic');
                            doc.addFileToVFS('Montserrat-bolditalic.ttf', montserratBoldItalic);
                            doc.addFont('Montserrat-bolditalic.ttf', 'Montserrat', 'bolditalic');
                            doc.addFileToVFS('Merriweather-normal.ttf', merriweatherRegular);
                            doc.addFont('Merriweather-normal.ttf', 'Merriweather', 'normal');
                            doc.addFileToVFS('Merriweather-bold.ttf', merriweatherBold);
                            doc.addFont('Merriweather-bold.ttf', 'Merriweather', 'bold');
                            doc.addFileToVFS('Merriweather-italic.ttf', merriweatherItalic);
                            doc.addFont('Merriweather-italic.ttf', 'Merriweather', 'italic');
                            doc.addFileToVFS('Merriweather-bolditalic.ttf', merriweatherBoldItalic);
                            doc.addFont('Merriweather-bolditalic.ttf', 'Merriweather', 'bolditalic');
                            doc.addFileToVFS('Katibeh-normal.ttf', katibeh);
                            doc.addFont('Katibeh-normal.ttf', 'Katibeh', 'normal');
                            doc.addFont('Katibeh-normal.ttf', 'Katibeh', 'bold');
                            doc.addFont('Katibeh-normal.ttf', 'Katibeh', 'italic');
                            doc.addFont('Katibeh-normal.ttf', 'Katibeh', 'bolditalic');
                            doc.addFileToVFS('Amiri-normal.ttf', Amiri);
                            doc.addFont('Amiri-normal.ttf', 'Amiri', 'normal');
                            doc.addFont('Amiri-normal.ttf', 'Amiri', 'bold');
                            doc.addFont('Amiri-normal.ttf', 'Amiri', 'italic');
                            doc.addFont('Amiri-normal.ttf', 'Amiri', 'bolditalic');
                            doc.addFileToVFS('Oswald-normal.ttf', oswald);
                            doc.addFont('Oswald-normal.ttf', 'Oswald', 'normal');
                            doc.addFont('Oswald-normal.ttf', 'Oswald', 'italic');
                            doc.addFileToVFS('Oswald-bold.ttf', oswaldBold);
                            doc.addFont('Oswald-bold.ttf', 'Oswald', 'bold');
                            doc.addFont('Oswald-bold.ttf', 'Oswald', 'bolditalic');
                            doc.addFileToVFS('MPLUS2-normal.ttf', MPLUS2);
                            doc.addFont('MPLUS2-normal.ttf', 'MPLUS2', 'normal');
                            doc.addFont('MPLUS2-normal.ttf', 'MPLUS2', 'bold');
                            doc.addFont('MPLUS2-normal.ttf', 'MPLUS2', 'italic');
                            doc.addFont( 'MPLUS2-normal.ttf', 'MPLUS2', 'bolditalic' );

                            if (background) {
                                if (orientation === 'portrait') {
                                    doc.addImage(background, "JPEG", 0, 0, 600, 900, '', 'NONE');
                                } else {
                                    doc.addImage(background, "JPEG", 0, 0, 900, 600, '', 'NONE');
                                }
                            }

                            certificate_fields = certificate_fields.sort((a, b) => (a.z || 2) - (b.z || 2));

                            const fieldPromises = certificate_fields.map((field) => {
                                if (!field.content) return Promise.resolve(null);

                                if (field.type === 'image') {
                                    return Promise.resolve(() => {
                                        doc.addImage(field.content, "JPEG", parseInt(field.x), parseInt(field.y), parseInt(field.w), parseInt(field.h));
                                    });
                                } else if (field.type === 'shape') {
                                    const shape = _this.getShapeById(field.content);
                                    if (shape && shape.svg) {
                                        return _this.convertSvgToImage(shape.svg, field.w, field.h, field.styles.color.hex)
                                            .then(svgAsImg => {
                                                return () => {
                                                    doc.addImage(svgAsImg, "PNG", parseInt(field.x), parseInt(field.y), parseInt(field.w), parseInt(field.h));
                                                };
                                            });
                                    }
                                } else if (field.type === 'qrcode') {
                                    return new Promise(resolve => {
                                        let tempDiv = document.createElement('div');
                                        document.body.appendChild(tempDiv);
                                        let qrCode = new QRCode(tempDiv, {
                                            text: _this.certificate_page + '?demo=yes',
                                            width: parseInt(field.w * 10),
                                            height: parseInt(field.h * 10),
                                            colorDark: "#000000",
                                            colorLight: "#ffffff",
                                            correctLevel: QRCode.CorrectLevel.H
                                        });
                                        let qrCanvas = tempDiv.querySelector('canvas');
                                        let qrImage = qrCanvas.toDataURL("image/jpeg");
                                        document.body.removeChild(tempDiv);
                                        resolve(() => {
                                            doc.addImage(qrImage, "JPEG", parseInt(field.x), parseInt(field.y), parseInt(field.w), parseInt(field.h));
                                        });
                                    });
                                } else {
                                    return Promise.resolve(() => {
                                        let textColor = _this.hexToRGB(field.styles.color.hex);
                                        let fontStyle = 'normal';
                                        let x = parseInt(field.x);
                                        const fontSize = parseInt(field.styles.fontSize.replace('px', ''));
                                        let y = parseInt(field.y) + fontSize;
                                        const fieldWidth = parseInt(field.w) - 12;
                                        const options = {
                                            maxWidth: fieldWidth,
                                            align: field.styles.textAlign,
                                            lineHeightFactor: 1.25
                                        };

                                        if (field.styles.textAlign === 'right') {
                                            x += fieldWidth;
                                        } else if (field.styles.textAlign === 'center') {
                                            x += 6 + fieldWidth / 2;
                                        } else {
                                            x += 6;
                                        }

                                        if (field.styles.fontWeight && field.styles.fontWeight !== "false") {
                                            fontStyle = 'bold';
                                            if (field.styles.fontStyle && field.styles.fontStyle !== "false") {
                                                fontStyle = 'bolditalic';
                                            }
                                        } else if (field.styles.fontStyle && field.styles.fontStyle !== "false") {
                                            fontStyle = 'italic';
                                        }

                                        doc.setTextColor(field.styles.color.hex);
                                        doc.setFontSize(fontSize * 1.4);
                                        doc.setFont(field.styles.fontFamily, fontStyle);
                                        doc.text(field.content, x, y, options);
                                    });
                                }
                                return Promise.resolve(null);
                            });

                            _this.refreshKey += 1;

                            Promise.all(fieldPromises).then((drawFunctions) => {
                                drawFunctions.forEach(drawFunc => {
                                    if (drawFunc) drawFunc();
                                });

                                _this.previewLoading = false;

                                if (preview) {
                                    const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
                                    if (isSafari) {
                                        doc.autoPrint();
                                        doc.output('save', 'Certificate.pdf');
                                        resolve();
                                    } else {
                                        window.open(doc.output('bloburl'));
                                        resolve();
                                    }
                                } else {
                                    resolve(doc.output('blob'));
                                }
                            }).catch(reject);
                        });
                    });
                },
                applyColorToSvg(svg, fieldIndex) {
                    const currentField = this.certificates[this.currentCertificate].data.fields[fieldIndex];
                    if (currentField.type !== 'shape') {
                        return;
                    }
                    const color = currentField.styles.color.hex || '#808C98';
                    const svgWithColor = svg.replace(/<path /g, `<path fill="${color}" `);
                    return svgWithColor;
                },
                convertSvgToImage(svgContent, containerWidth, containerHeight, newColor, scaleFactor = 2) {
                    return new Promise((resolve, reject) => {
                        let updatedSvgContent = svgContent.replace(/(<path[^>]*?)fill="[^"]*"/g, `$1fill="${newColor}"`);
                        const svgBlob = new Blob([updatedSvgContent], { type: 'image/svg+xml' });
                        const url = URL.createObjectURL(svgBlob);
                        const img = new Image();

                        img.onload = () => {
                            const originalWidth = img.width;
                            const originalHeight = img.height;
                            const adjustedWidth = containerWidth * scaleFactor;
                            const adjustedHeight = containerHeight * scaleFactor;
                            const scalingFactor = Math.min(adjustedWidth / originalWidth, adjustedHeight / originalHeight);
                            const newWidth = originalWidth * scalingFactor;
                            const newHeight = originalHeight * scalingFactor;

                            const canvas = document.createElement('canvas');
                            canvas.width = adjustedWidth;
                            canvas.height = adjustedHeight;

                            const context = canvas.getContext('2d');
                            context.clearRect(0, 0, adjustedWidth, adjustedHeight);
                            const offsetX = (adjustedWidth - newWidth) / 2;
                            const offsetY = (adjustedHeight - newHeight) / 2;

                            context.drawImage(img, offsetX, offsetY, newWidth, newHeight);
                            const base64Image = canvas.toDataURL('image/png', 1.0);
                            resolve(base64Image);
                        };
                        img.onerror = (error) => {
                            reject(new Error("Error converting SVG to PNG"));
                        };
                        img.src = url;
                    });
                },
                hexToRGB(h) {
                    let r = 0, g = 0, b = 0;

                    if (h.length == 4) {
                        r = "0x" + h[1] + h[1];
                        g = "0x" + h[2] + h[2];
                        b = "0x" + h[3] + h[3];
                    } else if (h.length == 7) {
                        r = "0x" + h[1] + h[2];
                        g = "0x" + h[3] + h[4];
                        b = "0x" + h[5] + h[6];
                    }

                    return {
                        r: r,
                        g: g,
                        b: b,
                    };
                },
                getDefaultCertificate() {
                    const defaultCertificate = this.certificates.find(certificate => 
                        certificate.id === parseInt(masterstudy_certificate_data.default_certificate)
                    );
                    if (defaultCertificate) {
                        this.defaultCertificate = defaultCertificate;
                        this.newDefaultCertificate = defaultCertificate;
                    }
                },
                getFields() {
                    const url = stm_lms_ajaxurl + '?action=stm_get_certificate_fields&nonce=' + stm_lms_nonces['stm_get_certificate_fields'];
                    return this.$http.get(url).then(function(res) {
                        this.$set(this, 'fields', res.body);
                    });
                },
                prevCategoriesPage(event) {
                    if (event.currentTarget.classList.contains('masterstudy-pagination__button_disabled')) {
                        return;
                    }
                    if (this.currentCategoriesPage > 1) {
                        this.currentCategoriesPage--;
                        this.getCategories(this.currentCategoriesPage);
                    }
                },
                nextCategoriesPage(event) {
                    if (event.currentTarget.classList.contains('masterstudy-pagination__button_disabled')) {
                        return;
                    }
                    if (this.currentCategoriesPage < this.totalCategoriesPages) {
                        this.currentCategoriesPage++;
                        this.getCategories(this.currentCategoriesPage);
                    }
                },
                changeCategoriesPage(page) {
                    this.currentCategoriesPage = page;
                    this.getCategories(this.currentCategoriesPage);
                },
                getCategories(page = 1, searchActive = false) {
                    this.loadingCategoriesPage = true;
                    const offset = (page - 1) * this.categoriesLimit;
                    const url = `${stm_lms_ajaxurl}?action=stm_get_certificate_categories&nonce=${stm_lms_nonces['stm_get_certificate_categories']}&offset=${offset}&search=${this.searchCategories}`;
                    this.$http.get(url).then(function(res) {
                        this.$set(this, 'categories', res.body.categories);
                        this.$set(this, 'totalCategories', res.body.total);
                        this.loadingCategoriesPage = false;
                        if ( searchActive ) {
                            this.searchCategoriesActive = true;
                            this.currentCategoriesPage = 1;
                        }
                    });
                },
                resetCategoriesSearch() {
                    this.searchCategories = '';
                    this.searchCategoriesActive = false;
                    this.currentCategoriesPage = 1;
                    this.getCategories();
                },
                resetCertificatesSearch() {
                    this.certificateSearchQuery = '';
                },
                openCreatePopup() {
                    this.createPopupVisible = true;
                },
                closeCreatePopup() {
                    this.createPopupVisible = false;
                },
                openImagePopup() {
                    this.imagePopupVisible = true;
                },
                openDeletePopupDefault() {
                    this.deleteAlertType = 'default';
                    this.deletePopupVisible = true;
                },
                openDeletePopupCertificate(index) {
                    this.deleteAlertType = 'certificate';
                    this.certificateForDelete = index;
                    this.deletePopupVisible = true;
                },
                openDeletePopupCategory(certificate, category) {
                    this.deleteAlertType = 'category';
                    this.certificateForDelete = certificate;
                    this.categoryForDelete = category;
                    this.deletePopupVisible = true;
                },
                closeDeletePopup() {
                    this.deletePopupVisible = false;
                },
                closeImagePopup() {
                    this.imagePopupVisible = false;
                },
                openCategoriesPopup(certificate, category) {
                    this.categoriesPopupVisible = true;
                    this.categoryCertificate = certificate;
                    this.newCategoryCertificate = certificate;
                    this.categoryToSave = category;
                },
                openCertificatesPopup() {
                    this.certificatesPopupVisible = true;
                    this.newDefaultCertificate = this.defaultCertificate;
                },
                closeCategoriesPopup() {
                    document.querySelector('.masterstudy-certificate-select-popup').classList.remove('masterstudy-certificate-select-popup_open');
                    const _this = this;
                    setTimeout(function() {
                        _this.categoriesPopupVisible = false;
                        _this.categoryCertificate = '';
                        _this.categoryToSave = '';
                        _this.newCategoryCertificate = '';
                    }, 300);
                },
                closeCategoriesPopupWithRedirect() {
                    this.closeCategoriesPopup();
                    this.changeCurrentTab('builder', true);
                },
                closeCertificatesPopup() {
                    const _this = this;
                    _this.certificatesPopupVisible = false;
                    setTimeout(function() {
                        _this.newDefaultCertificate = _this.defaultCertificate;
                    }, 300);
                },
                createPopupWrapperClick(event) {
                    if (!event.target.closest('.masterstudy-certificate-create-popup__wrapper')) {
                        this.closeCreatePopup();
                    }
                },
                deletePopupWrapperClick(event) {
                    if (!event.target.closest('.masterstudy-certificate-delete-popup__wrapper')) {
                        this.closeDeletePopup();
                    }
                },
                imagePopupWrapperClick(event) {
                    if (!event.target.closest('.masterstudy-certificate-image-popup__wrapper')) {
                        this.closeImagePopup();
                    }
                },
                certificatesPopupWrapperClick(event) {
                    if (!event.target.closest('.masterstudy-certificate-select-popup__wrapper')) {
                        this.closeCertificatesPopup();
                    }
                },
                categoriesPopupWrapperClick(event) {
                    if (!event.target.closest('.masterstudy-certificate-select-popup__wrapper')) {
                        this.closeCategoriesPopup();
                    }
                },
                editInBuilder(){
                    const index = this.certificates.findIndex(certificate => certificate.id === this.defaultCertificate.id);
                    this.$set(this, 'currentCertificate', index);
                    this.changeCurrentTab('builder');
                },
                addCertificate(orientation_type) {
                    const newCertificate = {
                        id: '',
                        title: 'New template',
                        thumbnail_id: '',
                        thumbnail: '',
                        image: '',
                        filename: '',
                        data: {
                            orientation: orientation_type,
                            category: '',
                            fields: []
                        }
                    };
                    const certificates = this.certificates;
                    certificates.unshift(newCertificate);
                    this.$set(this, 'certificates', certificates);
                    this.$set(this, 'currentCertificate', 0);
                    this.addActualTitle();
                    this.closeCreatePopup();
                    this.$nextTick(() => {
                        const content = document.querySelector('.masterstudy-certificate-templates__content');
                        if (content) {
                            content.scrollTop = 0;
                        }
                    });
                },
                uploadFieldImage(index) {
                    const _this = this;
                    const custom_uploader = wp.media({
                        title: "Select image",
                        button: {
                            text: "Attach"
                        },
                        multiple: true
                    }).on("select", function() {
                        const attachment = custom_uploader.state().get("selection").first().toJSON();
                        if (typeof _this.certificates[_this.currentCertificate].data.fields[index] !== 'undefined') {
                            _this.$set(_this.certificates[_this.currentCertificate].data.fields[index], 'imageId', attachment.id);
                            _this.$set(_this.certificates[_this.currentCertificate].data.fields[index], 'content', attachment.url);
                        }
                    }).open();
                },
                uploadImage() {
                    const _this = this;
                    const custom_uploader = wp.media({
                        title: "Select image",
                        button: {
                            text: "Attach"
                        },
                        multiple: true
                    }).on("select", function() {
                        const attachment = custom_uploader.state().get("selection").first().toJSON();
                        _this.certificates[_this.currentCertificate].thumbnail_id = attachment.id;
                        _this.certificates[_this.currentCertificate].thumbnail = attachment.url;
                        _this.certificates[_this.currentCertificate].filename = attachment.filename;
                    }).open();
                },
                handleFileUpload(event, background, index = '') {
                    const _this = this;
                    if (background) {
                        _this.notImageError = false;
                    } else {
                        _this.notImageErrorField = false;
                    }
                    if (event.target.files.length > 0) {
                        if (!event.target.files[0].type.startsWith('image/')) {
                            if (background) {
                                _this.notImageError = true;
                            } else {
                                _this.notImageErrorField = true;
                            }
                            return;
                        }
                        let formData = new FormData();
                        formData.append('action', 'stm_upload_certificate_images');
                        formData.append('image', event.target.files[0]);
                        formData.append('nonce', stm_lms_nonces['stm_upload_certificate_images']);

                        _this.$http.post(stm_lms_ajaxurl, formData, {}).then(function(response) {
                            if(response.body.image){
                                if (background) {
                                    _this.certificates[_this.currentCertificate].thumbnail_id = response.body.image.id;
                                    _this.certificates[_this.currentCertificate].thumbnail = response.body.image.url;
                                    _this.certificates[_this.currentCertificate].filename = response.body.image.filename;
                                } else {
                                    if (typeof _this.certificates[_this.currentCertificate].data.fields[index] !== 'undefined') {
                                        _this.$set(_this.certificates[_this.currentCertificate].data.fields[index], 'imageId', response.body.image.id);
                                        _this.$set(_this.certificates[_this.currentCertificate].data.fields[index], 'content', response.body.image.url);
                                    }
                                }
                            }
                        });
                    }
                },
                deleteImage(event) {
                    event.currentTarget.classList.add('masterstudy-button_loading');
                    this.certificates[this.currentCertificate].thumbnail_id = '';
                    this.certificates[this.currentCertificate].thumbnail = '';
                    this.certificates[this.currentCertificate].filename = '';
                    event.target.classList.remove('masterstudy-button_loading');
                    const anchor = event.target.closest('a');
                    if (anchor) {
                        anchor.classList.remove('masterstudy-button_loading');
                    }
                    this.closeImagePopup();
                },
                addField(event, type, fieldToDuplicate = null) {
                    if (event && event.currentTarget.classList.contains('masterstudy-certificate-controls__item_disabled')) {
                        return;
                    }
                    let content = '';
                    if (typeof this.fields[type] !== 'undefined') {
                        content = this.fields[type].value;
                    }
                    let x = 375;
                    if (typeof this.certificates[this.currentCertificate] !== 'undefined' && typeof this.certificates[this.currentCertificate].data.orientation !== 'undefined') {
                        const orientation = this.certificates[this.currentCertificate].data.orientation;
                        if(orientation === 'portrait'){
                            x = 225;
                        }
                    }
                    let height = 50;
                    let width = 150;
                    let zIndex = 2;
                    let styles = {
                        'fontSize': '14px',
                        'fontFamily': 'OpenSans',
                        'color': {
                            'hex': '#000'
                        },
                        'textAlign': 'left',
                        'fontStyle': 'normal',
                        'fontWeight': '400',
                    };
                    if (type === 'image') {
                        height = 150;
                    }
                    if (type === 'shape') {
                        height = 100;
                        width = 100;
                        styles = {
                            'color': {
                                'hex': '#808C98'
                            },
                        };
                    }
                    if (type === 'qrcode') {
                        height = 100;
                        width = 100;
                        styles = {};
                    }
                    if (fieldToDuplicate) {
                        content = fieldToDuplicate.content;
                        height = fieldToDuplicate.h;
                        width = fieldToDuplicate.w;
                        zIndex = fieldToDuplicate.z;
                        styles = { ...fieldToDuplicate.styles };
                    }
                    const field = {
                        'type': type,
                        'content': content,
                        'x': x,
                        'y': 0,
                        'w': width,
                        'h': height,
                        'styles': styles,
                        'classes': 'top-align',
                        'z': zIndex,
                    };
                    if (typeof this.certificates[this.currentCertificate] !== 'undefined') {
                        if(typeof this.certificates[this.currentCertificate].data.fields !== 'undefined'){
                            this.certificates[this.currentCertificate].data.fields.push(field);
                        }
                        else {
                            this.$set(this.certificates[this.currentCertificate].data, 'fields', [field])
                        }
                    }
                },
                duplicateField(index) {
                    const fieldToDuplicate = this.certificates[this.currentCertificate].data.fields[index];
                    if (fieldToDuplicate) {
                        this.addField(null, fieldToDuplicate.type, fieldToDuplicate);
                    }
                },
                increaseZIndex(index) {
                    const field = this.certificates[this.currentCertificate].data.fields[index];
                    if (field.z < 50) {
                        this.$set(field, 'z', field.z + 1);
                    }
                },
                decreaseZIndex(index) {
                    const field = this.certificates[this.currentCertificate].data.fields[index];
                    if (field.z > 1) {
                        this.$set(field, 'z', field.z - 1); 
                    }
                },
                deleteCertificate(event, index) {
                    event.currentTarget.classList.add('masterstudy-button_loading');
                    const certificates = this.certificates;
                    if (typeof certificates[index] !== 'undefined') {
                        if(typeof certificates[index].id !== 'undefined'){
                            const url = stm_lms_ajaxurl + '?action=stm_delete_certificate&nonce=' + stm_lms_nonces['stm_delete_certificate'] + '&certificate_id=' + certificates[index].id;
                            this.$http.get(url).then(function (res) {
                                certificates.splice(index, 1);
                                this.$set(this, 'certificates', certificates);
                                if (index !== 0) {
                                    this.$set(this, 'currentCertificate', index-1);
                                }
                                this.addActualTitle();
                                this.updateDefaultCertificate();
                                event.target.classList.remove('masterstudy-button_loading');
                                const anchor = event.target.closest('a');
                                if (anchor) {
                                    anchor.classList.remove('masterstudy-button_loading');
                                }
                                this.closeDeletePopup();
                            });
                        }
                    }
                },
                deleteField(index) {
                    const fields = this.certificates[this.currentCertificate].data.fields;
                    if (fields[index].type === 'shape') {
                        this.hideShapeSettings()
                    }
                    if (typeof fields[index] !== 'undefined') {
                        fields.splice(index, 1);
                        this.$set(this.certificates[this.currentCertificate].data, 'fields', fields);
                        this.activeField = null;
                        this.refreshKey += 1;
                    }
                },
                switchControlsTab(tabName) {
                    this.activeControlsTab = tabName;
                },
                openRightSidebar() {
                    this.openControlsSidebar = !this.openControlsSidebar;
                },
                updateCertificateTitle(newTitle) {
                    this.certificates[this.currentCertificate].title = newTitle;
                },
                addTitleActiveClass() {
                    this.titleActive = true;
                },
                removeTitleActiveClass() {
                    this.titleActive = false;
                },
                focusOnEditCertificate() {
                    const editableDiv = this.$refs.editCertificateTitle;
                    const range = document.createRange();
                    const sel = window.getSelection();
                    const childNodes = editableDiv.childNodes;
                    const lastChild = childNodes[childNodes.length - 1];

                    if (lastChild) {
                        range.selectNodeContents(editableDiv);
                        range.collapse(false);
                        sel.removeAllRanges();
                        sel.addRange(range);
                    }
                    editableDiv.focus();
                },
                previewGeneration() {
                    const _this = this;
                    const saveTasks = _this.certificates.map(certificate => {
                        return _this.generateCertificate(certificate, false).then(pdfBlob => {
                            const pdfUrl = URL.createObjectURL(pdfBlob);
                            const loadingTask = pdfjsLib.getDocument(pdfUrl);

                            return loadingTask.promise.then(pdf => {
                                return pdf.getPage(1);
                            }).then(page => {
                                const viewport = page.getViewport({scale: 1.0});
                                let canvas = document.createElement('canvas');
                                const ctx = canvas.getContext('2d');
                                canvas.height = viewport.height;
                                canvas.width = viewport.width;

                                const renderContext = {
                                    canvasContext: ctx,
                                    viewport: viewport
                                };

                                return page.render(renderContext).promise.then(() => {
                                    return new Promise((resolve, reject) => {
                                        canvas.toBlob(blob => {
                                            resolve({id: certificate.id, blob});
                                        }, 'image/jpeg');
                                    });
                                });
                            });
                        });
                    });

                    Promise.all(saveTasks).then(results => {
                        let formData = new FormData();
                        results.forEach((result, index) => {
                            formData.append(`previews[${index}][id]`, result.id);
                            formData.append(`previews[${index}][blob]`, result.blob, `preview-${result.id}.jpg`);
                        });
                        formData.append('action', 'stm_generate_certificates_preview');
                        formData.append('nonce', stm_lms_nonces['stm_generate_certificates_preview']);

                        _this.$http.post(stm_lms_ajaxurl, formData, {}).then(response => {
                            if (response.body.success) {
                                response.body.certificates.forEach(cert => {
                                    const index = _this.certificates.findIndex(c => c.id === cert.id);
                                    if (index !== -1) {
                                        _this.$set(_this.certificates[index], 'image', cert.image);
                                    }
                                });
                            }
                        });
                    })
                },
                saveCertificate() {
                    const _this = this;
                    _this.loadingSaveButton = true;

                    if (typeof _this.certificates[_this.currentCertificate] !== 'undefined') {
                        _this.certificates[_this.currentCertificate].data.fields.forEach((field, index) => {
                            if ((field.type === 'shape' || field.type === 'qrcode') && !field.content) {
                                field.content = 1;
                            }
                        });
                        _this.generateCertificate(_this.certificates[_this.currentCertificate], false)
                        .then(pdfBlob => {
                            const pdfUrl = URL.createObjectURL(pdfBlob);
                            const loadingTask = pdfjsLib.getDocument(pdfUrl);
                            loadingTask.promise.then(function(pdf) {
                                return pdf.getPage(1);
                            }).then(function(page) {
                                const viewport = page.getViewport({scale: 1.0});
                                let canvas = document.createElement('canvas');
                                const ctx = canvas.getContext('2d');
                                canvas.height = viewport.height;
                                canvas.width = viewport.width;
                                const renderContext = {
                                    canvasContext: ctx,
                                    viewport: viewport
                                };

                                return page.render(renderContext).promise.then(function() {
                                    return canvas;
                                });
                            }).then(function(canvas) {
                                canvas.toBlob(function(blob) {
                                    let formData = new FormData();
                                    formData.append('preview', blob, 'preview.jpg');
                                    formData.append('certificate', JSON.stringify(_this.certificates[_this.currentCertificate]));
                                    formData.append('action', 'stm_save_certificate');
                                    formData.append('nonce', stm_lms_nonces['stm_save_certificate']);

                                    _this.$http.post(stm_lms_ajaxurl, formData, {}).then(function(response) {
                                        if(typeof response.body.id !== 'undefined'){
                                            _this.$set(_this.certificates[_this.currentCertificate], 'id', response.body.id);
                                            _this.$set(_this.certificates[_this.currentCertificate], 'image', response.body.image);
                                        }
                                        _this.loadingSaveButton = false;
                                        _this.certificateSaved = true;
                                        setTimeout(function() {
                                            _this.certificateSaved = false;
                                        }, 1000);
                                    });
                                }, 'image/jpeg');
                            });
                        });
                    }
                },
                saveCertificateCategory(event) {
                    if (event.currentTarget.classList.contains('masterstudy-button_disabled')) {
                        return;
                    }
                    this.loadingSaveButton = true;
                    const data = {
                        old_certificate: this.categoryCertificate,
                        new_certificate: this.newCategoryCertificate,
                        category: this.categoryToSave,
                        action: 'stm_save_certificate_category',
                        nonce: stm_lms_nonces['stm_save_certificate_category'],
                    };
                    this.$http.post(stm_lms_ajaxurl, data, {emulateJSON: true}).then(function(response) {
                        if (response.data === "saved") {
                            const _this = this;
                            _this.loadingSaveButton = false;
                            document.querySelector('.masterstudy-certificate-select-popup').classList.remove('masterstudy-certificate-select-popup_open');
                            setTimeout(function() {
                                const certificateToAdd = _this.certificates.find(certificate => certificate.id === _this.newCategoryCertificate.id);
                                if (certificateToAdd.data.category && certificateToAdd.data.category.length > 0) {
                                    let categoriesArray = certificateToAdd.data.category.split(',');
                                    if (!categoriesArray.includes(_this.categoryToSave)) {
                                        categoriesArray.push(_this.categoryToSave);
                                        certificateToAdd.data.category = categoriesArray.join(',');
                                    }
                                } else {
                                    certificateToAdd.data.category = _this.categoryToSave;
                                }
                                if ( _this.categoryCertificate ) {
                                    const certificateToDelete = _this.certificates.find(certificate => certificate.id === _this.categoryCertificate.id);
                                    if (certificateToDelete && certificateToDelete.data) {
                                        let categories = certificateToDelete.data.category.split(',');
                                        categories = categories.filter(categoryId => categoryId !== _this.categoryToSave);
                                        certificateToDelete.data.category = categories.join(',');
                                    }
                                }
                                _this.categoriesPopupVisible = false;
                                _this.categoryCertificate = '';
                                _this.categoryToSave = '';
                                _this.newCategoryCertificate = '';
                            }, 300 );
                        }
                    });
                },
                unlinkCategory(event, certificate, certCategory) {
                    event.currentTarget.classList.add('masterstudy-button_loading');
                    const data = {
                        certificate: certificate,
                        category: certCategory,
                        action: 'stm_delete_certificate_category',
                        nonce: stm_lms_nonces['stm_delete_certificate_category'],
                    };
                    this.$http.post(stm_lms_ajaxurl, data, {emulateJSON: true}).then(function(response) {
                        if (response.data === "saved") {
                            const certificateToDelete = this.certificates.find(certif => certif.id === certificate.id);
                            if (certificateToDelete && certificateToDelete.data) {
                                let categories = certificateToDelete.data.category.split(',');
                                categories = categories.filter(categoryId => categoryId !== certCategory);
                                certificateToDelete.data.category = categories.join(',');
                                this.categoryCertificate = '';
                                this.newCategoryCertificate = '';
                            }
                        }
                        event.target.classList.remove('masterstudy-button_loading');
                        const anchor = event.target.closest('a');
                        if (anchor) {
                            anchor.classList.remove('masterstudy-button_loading');
                        }
                        this.closeDeletePopup();
                    });
                },
                checkDefaultCertificate(certificate) {
                    this.newDefaultCertificate = certificate;
                },
                checkCertificateCategory(certificate) {
                    this.newCategoryCertificate = certificate;
                },
                deleteDefaultCertificate(event) {
                    event.currentTarget.classList.add('masterstudy-button_loading');
                    const data = {
                        action: 'stm_delete_default_certificate',
                        nonce: stm_lms_nonces['stm_delete_default_certificate'],
                    };
                    this.$http.post(stm_lms_ajaxurl, data, {emulateJSON: true}).then(function(response) {
                        if (response.data === "deleted") {
                            this.defaultCertificate = '';
                        }
                        event.target.classList.remove('masterstudy-button_loading');
                        const anchor = event.target.closest('a');
                        if (anchor) {
                            anchor.classList.remove('masterstudy-button_loading');
                        }
                        this.closeDeletePopup();
                    });
                },
                saveDefaultCertificate(event) {
                    if (event.currentTarget.classList.contains('masterstudy-button_disabled')) {
                        return;
                    }
                    this.loadingSaveButton = true;
                    const data = {
                        new_certificate: this.newDefaultCertificate.id,
                        action: 'stm_save_default_certificate',
                        nonce: stm_lms_nonces['stm_save_default_certificate'],
                    };
                    this.$http.post(stm_lms_ajaxurl, data, {emulateJSON: true}).then(function(response) {
                        if (response.data === "saved") {
                            this.defaultCertificate = this.newDefaultCertificate;
                            this.closeCertificatesPopup();
                        }
                        this.loadingSaveButton = false;
                    });
                },
                updateDefaultCertificate() {
                    const defaultCertExists = this.savedCertificates.some(certificate => certificate.id === this.defaultCertificate.id);
                    if (!defaultCertExists) {
                        this.defaultCertificate = '';
                    }
                },
                activateField(key) {
                    this.activeField = key;
                    if (this.$refs[`shapeField-${key}`] && this.$refs[`shapeField-${key}`][0]) {
                        this.activeFieldShapeElement = this.$refs[`shapeField-${key}`][0];
                    } else {
                        this.activeFieldShapeElement = null;
                    }
                },
                onDragStart() {
                    this.applyClassToTextFields(true, 'masterstudy-select-none');
                    this.hideShapeSettings();
                },
                onResizeStart() {
                    this.applyClassToTextFields(true, 'masterstudy-select-none');
                    this.hideShapeSettings();
                },
                onResizeStop(left, top, width, height, key) {
                    this.onResize(left, top, width, height);
                    this.onResizeEnd();
                    const currentField = this.certificates[this.currentCertificate].data.fields[key];
                    if (currentField.type === 'shape') {
                        this.shapeSettingsPosition(left, top, key);
                    }
                },
                onDragStop(left, top, key) {
                    this.onDrag(left, top, key);
                    this.onDragEnd();
                    const currentField = this.certificates[this.currentCertificate].data.fields[key];
                    if (currentField.type === 'shape') {
                        this.shapeSettingsPosition(left, top, key);
                    }
                },
                onDragEnd() {
                    this.applyClassToTextFields(false, 'masterstudy-select-none');
                },
                onDrag(left, top, key) {
                    let leftBoundary, rightBoundary;
                    if (typeof this.certificates[this.currentCertificate].data.fields[this.activeField] !== 'undefined') {
                        let classes = '';
                        if (this.certificates[this.currentCertificate].data.orientation === 'landscape') {
                            leftBoundary = 48;
                            rightBoundary = 700;
                        } else {
                            leftBoundary = 50;
                            rightBoundary = 395;
                        }

                        if (left < leftBoundary) {
                            classes = 'left-align';
                        } else if (left > rightBoundary) {
                            classes = 'right-align';
                        }

                        if(top < 100){
                            if (classes.length > 0) {
                                classes += ' ';
                            }
                            classes += 'top-align';
                        }
                        const currentField = this.certificates[this.currentCertificate].data.fields[this.activeField];
                        this.$set(currentField, 'x', left);
                        this.$set(currentField, 'y', top);
                        this.$set(currentField, 'classes', classes);
                        this.colorPickerPosition(left, top);
                        if (currentField.type === 'shape') {
                            this.shapeSettingsPosition(left, top, key);
                        }
                    }
                },
                onResize(left, top, width, height) {
                    if (typeof this.certificates[this.currentCertificate].data.fields[this.activeField] !== 'undefined') {
                        const activeField = this.certificates[this.currentCertificate].data.fields[this.activeField];
                        this.$set(activeField, 'x', left);
                        this.$set(activeField, 'y', top);
                        this.$set(activeField, 'w', width);
                        this.$set(activeField, 'h', height);
                    }
                },
                onResizeEnd() {
                    this.applyClassToTextFields(false, 'masterstudy-select-none');
                },
                applyClassToTextFields(addClass, className) {
                    const elements = document.querySelectorAll('.masterstudy-certificate-canvas-wrap__zone textarea, .masterstudy-certificate-canvas-wrap__zone img');
                    elements.forEach((element) => {
                        if (addClass) {
                            if (!element.classList.contains(className)) {
                                element.classList.add(className);
                            }
                        } else {
                            if (element.classList.contains(className)) {
                                element.classList.remove(className);
                            }
                            if (element.tagName.toLowerCase() === 'textarea' || element.tagName.toLowerCase() === 'input') {
                                element.blur();
                            }
                        }
                    });
                },
                handleFieldClick(left, top) {
                    this.colorPickerPosition(left, top);
                },
                handleFieldShapeClick(left, top, key) {
                    this.colorPickerPosition(left, top);
                    const currentField = this.certificates[this.currentCertificate].data.fields[key];
                    if (currentField && currentField.type === 'shape') {
                        this.shapeSettingsPosition(left, top, key);
                    }
                },
                shapeSettingsPosition(left, top, key) {
                    this.ActiveShapefieldKey = key;
                    this.ActiveShapefield = this.certificates[this.currentCertificate].data.fields[key];
                    if (!this.ActiveShapefield || this.ActiveShapefield.type !== 'shape') {
                        return;
                    }
                    const shapeSettingsElement = document.querySelector('.masterstudy-shape-settings');
                    if (shapeSettingsElement) {
                        const activeFieldWidth = this.ActiveShapefield.w;
                        const shapeSettingsWidth = shapeSettingsElement.offsetWidth;
                        const centeredLeft = left + (activeFieldWidth / 2) - (shapeSettingsWidth / 2);
                        shapeSettingsElement.style.position = 'absolute';
                        shapeSettingsElement.style.transform = `translate(${centeredLeft}px, ${top}px)`;
                        shapeSettingsElement.style.opacity = '1';
                        shapeSettingsElement.style.visibility = 'visible';
                    }
                },
                hideShapeSettings() {
                    const shapeSettingsElement = document.querySelector('.masterstudy-shape-settings');
                    if (shapeSettingsElement) {
                        shapeSettingsElement.style.opacity = '0';
                        shapeSettingsElement.style.visibility = 'hidden';
                        this.ActiveShapefieldKey = null;
                        this.ActiveShapefield = null;
                    }
                },
                handleClickOutsideShapeSettings(event) {
                    const clickedOutsideField = !event.target.closest('.draggable.resizable') && !event.target.closest('.masterstudy-shape-settings');

                    if (!this.activeFieldShapeElement || clickedOutsideField) {
                        this.hideShapeSettings();
                    }
                },
                colorPickerPosition(left, top) {
                    const isLandscape = this.certificates[this.currentCertificate].data.orientation === 'landscape';
                    let fieldWidth = 515,
                    fieldHeight = 310,
                    leftPosition = 0,
                    topPosition = 0,
                    dragAreaWidth, dragAreaHeight, leftCoef, topCoef;
                    if (typeof this.certificates[this.currentCertificate].data.fields[this.activeField] !== 'undefined') {
                        if (isLandscape) {
                            dragAreaWidth = 900;
                            dragAreaHeight = 591;
                            leftCoef = 70;
                            topCoef = 70;
                        } else {
                            fieldWidth = 288
                            fieldHeight = 577
                            dragAreaWidth = 591;
                            dragAreaHeight = 900;
                            leftCoef = 70;
                            topCoef = 50;
                        }

                        if (left + fieldWidth > dragAreaWidth) {
                            leftPosition = dragAreaWidth - fieldWidth - leftCoef;
                        }

                        if (top + fieldHeight > dragAreaHeight) {
                            topPosition = dragAreaHeight - fieldHeight - topCoef;
                        }

                        if (!isLandscape && top > 550) {
                            topPosition = 500;
                        }

                        const colorPicker = document.querySelector('.active.draggable.resizable .field-content .vc-photoshop');
                        if ( colorPicker ) {
                            colorPicker.style.top = `-${topPosition}px`;
                            colorPicker.style.left = `-${leftPosition}px`;
                        }
                    }
                },
                toggleShapeDropdown() {
                    this.dropdownShapeOpen = !this.dropdownShapeOpen;
                    this.colorPickerVisible = false;
                    if (this.dropdownShapeOpen) {
                        document.addEventListener('click', this.handleClickOutsideShape);
                    } else {
                        document.removeEventListener('click', this.handleClickOutsideShape);
                    }
                },
                handleClickOutsideShape(event) {
                    const dropdown = this.$el.querySelector('.masterstudy-shape-dropdown');
                    if (dropdown && !dropdown.contains(event.target)) {
                      this.dropdownShapeOpen = false;
                      document.removeEventListener('click', this.handleClickOutsideShape);
                    }
                },
                getShapeById(shapeId) {
                    return this.shapes.find(shape => shape.id === shapeId);
                },
                selectShape(shapeId, index) {
                    this.$set(this.certificates[this.currentCertificate].data.fields[index], 'content', shapeId);
                    this.dropdownShapeOpen = false;
                    document.removeEventListener('click', this.handleClickOutsideShape);
                  }
            }
        });
    });
})(jQuery);