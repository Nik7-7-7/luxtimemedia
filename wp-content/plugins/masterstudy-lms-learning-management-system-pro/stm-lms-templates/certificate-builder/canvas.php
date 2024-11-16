<div v-if="certificates[currentCertificate]" class="masterstudy-certificate-canvas__axis-vertical-wrapper"></div>
<div class="masterstudy-certificate-canvas" dir="ltr">
	<div v-if="certificates[currentCertificate]" class="masterstudy-certificate-canvas__axis-horizontal-wrapper">
		<img
			v-if="certificates[currentCertificate]?.data?.orientation === 'landscape'"
			src="<?php echo esc_url( STM_LMS_PRO_URL . 'assets/img/certificate-builder/long-axis-horizontal.svg' ); ?>"
			class="masterstudy-certificate-canvas__axis-horizontal landscape-axis"
		/>
		<img
			v-else
			src="<?php echo esc_url( STM_LMS_PRO_URL . 'assets/img/certificate-builder/short-axis-horizontal.svg' ); ?>"
			class="masterstudy-certificate-canvas__axis-horizontal"
		/>
	</div>
	<div
		v-if="certificates[currentCertificate]"
		class="masterstudy-certificate-canvas__wrapper"
		:class="certificates[currentCertificate]?.data?.orientation ?? 'landscape'"
		dir="ltr"
	>
		<div class="masterstudy-certificate-canvas-wrap">
			<img
				v-if="certificates[currentCertificate] && certificates[currentCertificate]?.data?.orientation === 'landscape'"
				src="<?php echo esc_url( STM_LMS_PRO_URL . 'assets/img/certificate-builder/short-axis-vertical.svg' ); ?>"
				class="masterstudy-certificate-canvas__axis-vertical landscape-axis"
			/>
			<img
				v-if="certificates[currentCertificate] && certificates[currentCertificate]?.data?.orientation === 'portrait'"
				src="<?php echo esc_url( STM_LMS_PRO_URL . 'assets/img/certificate-builder/long-axis-vertical.svg' ); ?>"
				class="masterstudy-certificate-canvas__axis-vertical"
			/>
			<div :class="(certificates[currentCertificate]?.data?.orientation ?? 'landscape') + ' canvas-wrap'">
				<span class="masterstudy-certificate-canvas__dash masterstudy-certificate-canvas__dash_top"></span>
				<span class="masterstudy-certificate-canvas__dash masterstudy-certificate-canvas__dash_bottom"></span>
				<span class="masterstudy-certificate-canvas__dash masterstudy-certificate-canvas__dash_left"></span>
				<span class="masterstudy-certificate-canvas__dash masterstudy-certificate-canvas__dash_right"></span>
				<div :key="refreshKey" class="masterstudy-certificate-canvas-wrap__zone">
					<div class="masterstudy-certificate-canvas-background" v-if="certificates[currentCertificate]?.thumbnail">
						<img :src="certificates[currentCertificate].thumbnail" class="masterstudy-certificate-canvas-background__image" />
					</div>
					<vue-draggable-resizable
						v-if="certificates[currentCertificate]?.data?.fields !== undefined"
						:parent="field.type !== 'shape'"
						:class="{'shape': field.type === 'shape'}"
						v-for="(field, key) in certificates[currentCertificate].data.fields"
						:key="`draggable-${certificates[currentCertificate].id}-${key}`"
						:w="field.w"
						:h="field.h"
						:x="field.x"
						:y="field.y"
						:z="field.z !== undefined ? field.z : 2"
						:lock-aspect-ratio="field.type === 'qrcode'"
						drag-cancel=".settings"
						@dragging="onDragStart"
						@resizing="onResizeStart"
						@resizestop="(left, top, width, height) => onResizeStop(left, top, width, height, key)"
						@activated="activateField(key)"
						@dragstop="(left, top) => onDragStop(left, top, key)"
					>
						<div v-if="field.type === 'image'" class="image-wrap">
							<img v-if="typeof field.imageId !== 'undefined'" v-bind:src="field.content"/>
							<div v-if="isAdmin && typeof field.imageId === 'undefined'" class="uploader">
								<span @click="uploadFieldImage(key)">
									<?php echo esc_html__( 'Select Image', 'masterstudy-lms-learning-management-system-pro' ); ?>
								</span>
							</div>
							<div v-if="!isAdmin && typeof field.imageId === 'undefined'" class="uploader">
								<label :for="`field_image_${key}`">
									<?php echo esc_html__( 'Select Image', 'masterstudy-lms-learning-management-system-pro' ); ?>
								</label>
							</div>
							<input v-if="!isAdmin" type="file" :id="`field_image_${key}`" :name="`field_image_${key}`" @change="handleFileUpload($event, false, key)" class="image-field-input">
							<span v-if="notImageErrorField" class="image-field-error">
								<?php echo esc_html__( 'Only images allowed', 'masterstudy-lms-learning-management-system-pro' ); ?>
							</span>
							<i class="fa fa-trash" @click="deleteField(key)" title="<?php echo esc_attr__( 'Delete field', 'masterstudy-lms-learning-management-system-pro' ); ?>"></i>
						</div>
						<div v-else-if="field.type === 'qrcode'" class="masterstudy-qr-wrap">
							<div class="masterstudy-qr-wrapper">
								<img v-if="certificate_page" class="masterstudy-qr-preview" src="<?php echo esc_url( STM_LMS_PRO_URL . 'assets/img/certificate-builder/qrcode.png' ); ?>" />
								<img v-else class="masterstudy-qr-preview" src="<?php echo esc_url( STM_LMS_PRO_URL . 'assets/img/certificate-builder/qrcode-error.png' ); ?>"/>
							</div>
							<div v-if="!certificate_page" class="masterstudy-qr-error">
								<span v-if="isAdmin" class="masterstudy-qr-error__text">
									<?php echo esc_html__( 'Certificate page is not specified, please specify it for correct display', 'masterstudy-lms-learning-management-system-pro' ); ?>
								</span>
								<span v-else class="masterstudy-qr-error__text">
									<?php echo esc_html__( 'Certificate page is not specified, please contact the administrator', 'masterstudy-lms-learning-management-system-pro' ); ?>
								</span>
								<div v-if="isAdmin" class="masterstudy-qr-error__action">
									<?php
									STM_LMS_Templates::show_lms_template(
										'components/button',
										array(
											'title'  => esc_html__( 'Set certiicate page', 'masterstudy-lms-learning-management-system' ),
											'link'   => admin_url( 'admin.php?page=stm-lms-settings#section_routes' ),
											'style'  => 'primary',
											'size'   => 'sm',
											'id'     => 'certificate-page-link',
											'target' => '_blank',
										)
									);
									?>
								</div>
							</div>
							<i class="fa fa-trash" @click="deleteField(key)" title="<?php echo esc_attr__( 'Delete field', 'masterstudy-lms-learning-management-system-pro' ); ?>"></i>
						</div>
						<div
							v-else-if="field.type === 'shape'"
							:ref="`shapeField-${key}`"
							class="masterstudy-shape-wrap"
							@click="handleFieldShapeClick(field.x, field.y, key)"
						>
							<div class="masterstudy-shape-wrapper">
								<div class="masterstudy-shape-preview" v-if="getShapeById(field.content)" v-html="applyColorToSvg(getShapeById(field.content).svg, key)"></div>
								<div class="masterstudy-shape-preview" v-else v-html="applyColorToSvg(shapes[0].svg, key)"></div>
							</div>
							<i class="fa fa-trash" @click="deleteField(key)" title="<?php echo esc_attr__( 'Delete field', 'masterstudy-lms-learning-management-system-pro' ); ?>"></i>
						</div>
						<div
							v-else
							:class="[
								'field-content',
								field.classes,
								{ 'field-fullwidth': certificates[currentCertificate]?.data?.orientation === 'portrait' ? field.w > 580 : field.w > 890 }
							]"
							@click="handleFieldClick(field.x, field.y)"
						>
							<textarea v-model="field.content"
									:readonly="field.type !== 'text'"
									v-bind:style="{
									'fontSize': field.styles.fontSize,
									'fontFamily': field.styles.fontFamily === 'OpenSans' ? 'Open Sans' : field.styles.fontFamily,
									'color': field.styles.color.hex,
									'textAlign': field.styles.textAlign,
									'textDecoration': field.styles.textDecoration ? 'underline' : 'none',
									'fontStyle': (field.styles.fontStyle && field.styles.fontStyle !== 'false') ? 'italic' : 'normal',
									'fontWeight': (field.styles.fontWeight && field.styles.fontWeight !== 'false') ? 'bold' : '400',
									}"
							></textarea>
							<div class="settings">
								<div class="font">
									<select v-model="field.styles.fontFamily">
										<option value="OpenSans">OpenSans</option>
										<option value="Montserrat">Montserrat</option>
										<option value="Merriweather">Merriweather</option>
										<option value="Katibeh">Katibeh (arab)</option>
										<option value="Amiri">Amiri (arab)</option>
										<option value="Oswald">Oswald</option>
										<option value="MPLUS2">M PLUS 2</option>
									</select>
									<select v-model="field.styles.fontSize">
										<option value="8px">8px</option>
										<option value="10px">10px</option>
										<option value="12px">12px</option>
										<option value="14px">14px</option>
										<option value="16px">16px</option>
										<option value="18px">18px</option>
										<option value="20px">20px</option>
										<option value="24px">24px</option>
										<option value="28px">28px</option>
										<option value="32px">32px</option>
										<option value="40px">40px</option>
										<option value="60px">60px</option>
										<option value="80px">80px</option>
										<option value="100px">100px</option>
									</select>
								</div>
								<div class="font-style" @click="colorPickerShow()">
									<div class="color">
										<div class="color-value">
											<div v-bind:style="{'backgroundColor': typeof field.styles.color.hex !== 'undefined' ? field.styles.color.hex : '#000'}"></div>
										</div>
										<photoshop-picker v-show="colorPickerVisible" v-model="field.styles.color"></photoshop-picker>
									</div>
									<div class="align">
										<div class="checkbox-wrap">
											<input v-bind:id="'text-align-left-' + key" type="radio" v-model="field.styles.textAlign" value="left"/>
											<label class="left" v-bind:for="'text-align-left-' + key">
												<i class="fa fa-align-left"></i>
											</label>
										</div>
										<div class="checkbox-wrap">
											<input v-bind:id="'text-align-center-' + key" type="radio" v-model="field.styles.textAlign" value="center"/>
											<label class="center" v-bind:for="'text-align-center-' + key">
												<i class="fa fa-align-center"></i>
											</label>
										</div>
										<div class="checkbox-wrap">
											<input v-bind:id="'text-align-right-' + key" type="radio" v-model="field.styles.textAlign" value="right"/>
											<label class="right" v-bind:for="'text-align-right-' + key">
												<i class="fa fa-align-right"></i>
											</label>
										</div>
									</div>
									<div class="decoration">
										<div class="checkbox-wrap">
											<input v-bind:id="'font-weight-bold-' + key" type="checkbox" v-model="field.styles.fontWeight" value="bold"/>
											<label v-bind:for="'font-weight-bold-' + key">
												<i class="fa fa-bold"></i>
											</label>
										</div>
										<div class="checkbox-wrap">
											<input v-bind:id="'font-style-italic-' + key" type="checkbox" v-model="field.styles.fontStyle" value="italic"/>
											<label v-bind:for="'font-style-italic-' + key">
												<i class="fa fa-italic"></i>
											</label>
										</div>
									</div>
								</div>
							</div>
							<i class="fa fa-trash" @click="deleteField(key)" title="<?php echo esc_attr__( 'Delete field', 'masterstudy-lms-learning-management-system-pro' ); ?>"></i>
						</div>
					</vue-draggable-resizable>
					<div class="masterstudy-shape-settings">
						<div
							v-if="ActiveShapefield && ActiveShapefieldKey !== null"
							:class="{'masterstudy-shape-select': true, 'masterstudy-shape-select_active': dropdownShapeOpen}"
							@click.stop="toggleShapeDropdown"
						>
							<div class="masterstudy-shape-selected">
							<div class="masterstudy-shape-selected-item"
								v-if="getShapeById(ActiveShapefield.content)"
								v-html="applyColorToSvg(getShapeById(ActiveShapefield.content).svg, ActiveShapefieldKey)">
							</div>
							<div class="masterstudy-shape-selected-item"
								v-else
								v-html="applyColorToSvg(shapes[0].svg, ActiveShapefieldKey)">
							</div>
							</div>
							<div v-if="dropdownShapeOpen" class="masterstudy-shape-dropdown">
								<div v-for="(shape, index) in shapes" @click.stop="selectShape(shape.id, ActiveShapefieldKey)" class="masterstudy-shape-dropdown__item" :key="shape.id">
									<div v-html="applyColorToSvg(shape.svg, ActiveShapefieldKey)"></div>
								</div>
							</div>
						</div>
						<div v-if="ActiveShapefield && ActiveShapefieldKey !== null" class="font-style" @click="colorPickerShow()">
							<div class="color">
								<div class="color-value">
									<div v-bind:style="{'backgroundColor': typeof ActiveShapefield.styles.color.hex !== 'undefined' ? ActiveShapefield.styles.color.hex : '#808C98'}"></div>
								</div>
								<photoshop-picker v-show="colorPickerVisible" v-model="ActiveShapefield.styles.color"></photoshop-picker>
							</div>
							<div class="masterstudy-z-index-control">
								<span @click="decreaseZIndex(ActiveShapefieldKey)" class="masterstudy-z-index-btn masterstudy-z-index-btn_bottom">−</span>
								<img src="<?php echo esc_url( STM_LMS_PRO_URL . 'assets/img/certificate-builder/z-index.svg' ); ?>" class="masterstudy-z-index-icon">
								<input 
									type="text" 
									:value="ActiveShapefield.z"
									readonly
									class="masterstudy-z-index-input"
								/>
								<span @click="increaseZIndex(ActiveShapefieldKey)" class="masterstudy-z-index-btn masterstudy-z-index-btn_top">+</span>
							</div>
							<div class="masterstudy-duplicate-button" @click="duplicateField(ActiveShapefieldKey)">
								<img src="<?php echo esc_url( STM_LMS_PRO_URL . 'assets/img/certificate-builder/duplicate.svg' ); ?>" class="masterstudy-duplicate-icon">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="masterstudy-certificate-canvas__actions" v-if="certificates[currentCertificate]?.id !== undefined">
			<div class="masterstudy-certificate-canvas__actions-wrapper">
				<div
					ref="titleContainer"
					class="masterstudy-certificate-canvas__actions-title"
					:class="{'masterstudy-certificate-canvas__actions-title_active': titleActive}"
					@click="addTitleActiveClass()"
				>
					<div
						contenteditable="true"
						ref="editCertificateTitle"
						@input="updateCertificateTitle($event.target.textContent)"
						class="masterstudy-certificate-canvas__actions-input"
					>
					</div>
					<span v-if="!titleActive" class="masterstudy-certificate-canvas__actions-icon" @click="focusOnEditCertificate()"></span>
					<span v-else class="masterstudy-certificate-canvas__actions-icon-accept" @click.stop="removeTitleActiveClass()"></span>
				</div>
				<a
					v-if="certificates[currentCertificate]?.id"
					href="#"
					class="masterstudy-button masterstudy-button_style-tertiary masterstudy-button_size-sm masterstudy_preview_certificate"
					:class="{'masterstudy-button_loading': previewLoading}"
					@click.prevent="previewCertificate($event)"
					:data-id="certificates[currentCertificate].id"
				>
					<span class="masterstudy-button__title">
						<?php echo esc_html__( 'Preview', 'masterstudy-lms-learning-management-system-pro' ); ?>
					</span>
				</a>
				<a
					href="#"
					class="masterstudy-button masterstudy-button_style-primary masterstudy-button_size-sm"
					:class="{'masterstudy-button_loading': loadingSaveButton}"
					@click.prevent="saveCertificate()"
				>
					<span v-if="certificateSaved" class="masterstudy-button__title">
						<?php echo esc_html__( 'Saved!', 'masterstudy-lms-learning-management-system-pro' ); ?>
					</span>
					<span v-else class="masterstudy-button__title">
						<?php echo esc_html__( 'Save Certificate', 'masterstudy-lms-learning-management-system-pro' ); ?>
					</span>
				</a>
			</div>
		</div>
	</div>
</div>
