<section class="avada-db-card">
	<div class="fusion-builder-option">
		<div class="fusion-builder-option-title">
			<h2><?php esc_html_e( 'Role Manager', 'fusion-builder' ); ?></h2>
			<span class="fusion-builder-option-label">
				<p>
					<?php esc_html_e( 'Manage access to various Avada components based on user roles. If you reset a native WordPress user role, the default access levels will be restored. If you reset a custom user role, all values will be greyed out, indicating that access will be denied or granted based on the capabilities set for these roles.', 'fusion-builder' ); ?>
				</p>
			</span>
		</div>

		<div class="fusion-builder-option-field">
			<?php $default_capabilities = $this->get_default_role_manager_capabilities(); ?>
			<?php foreach ( $roles as $role ) : ?>
				<?php $role_name = strtolower( str_replace( [ ' ', '-' ], '_', $role['name'] ) ); ?>
				<div class="awb-role-manager-item">
					<div class="awb-role-manager-item-title" data-target="<?php echo esc_attr( $role_name ); ?>">
						<span class="awb-role-manager-role-name"><?php echo esc_html( translate_user_role( $role['name'] ) ); ?></span>
						<button class="button awb-role-manager-reset-role"><?php esc_html_e( 'Reset Role', 'fusion-builder' ); ?></button>
					</div>
					<div id="<?php echo esc_attr( $role_name ); ?>" class="awb-role-manager-item-accordion">
						<div class="awb-role-manager-item-accordion-body">
							<ul>
								<?php foreach ( $post_types as $post_type ) : ?>
									<?php
									$maybe_disabled = '';
									?>
									<li class="title-label">
										<div class="awb-role-manager-cpt-label"><?php echo esc_html( $this->get_post_type_label( $post_type ) ); ?></div>
										<ul class="awb-role-manager-access-items">
										<?php if ( ! in_array( $post_type->name, $this->get_non_avada_post_types(), true ) ) : ?>
											<?php $maybe_disabled = 'on' === $this->get_option_page_value( $role_name, $post_type->name, 'dashboard_access' ) ? '' : ' awb-disabled'; ?>
											<li class="awb-role-manager-access-item awb-dashboard-access">
												<div class="fusion-builder-option-field">
													<div class="awb-role-manager-item-label"><?php esc_html_e( 'Dashboard Access', 'fusion-builder' ); ?></div>
													<div class="fusion-form-radio-button-set ui-buttonset enable-builder-ui">
														<?php $value = $this->get_option_page_value( $role_name, $post_type->name, 'dashboard_access' ); ?>
														<?php $default_access = isset( $default_capabilities[ $role_name ][ $post_type->name ]['dashboard_access'] ) ? $default_capabilities[ $role_name ][ $post_type->name ]['dashboard_access'] : ''; ?>
														<input type="checkbox" <?php echo $value ? 'checked' : ''; ?> data-default="<?php echo esc_attr( $default_access ); ?>" class="awb-hidden button-set-value awb-role-manager-dashboard-access" name="role_manager_caps[<?php echo esc_attr( $role_name ); ?>][<?php echo esc_attr( $post_type->name ); ?>][dashboard_access]" value="<?php echo esc_attr( $value ); ?>" />
														<a data-value="on" class="ui-button buttonset-item<?php echo ( 'on' === $value ) ? ' ui-state-active' : ''; ?>" href="#"><?php esc_html_e( 'On', 'fusion-builder' ); ?></a>
														<a data-value="off" class="ui-button buttonset-item<?php echo ( 'off' === $value ) ? ' ui-state-active' : ''; ?>" href="#"><?php esc_html_e( 'Off', 'fusion-builder' ); ?></a>
													</div>
												</div>
											</li>   
										<?php endif; ?>
										<?php if ( ! in_array( $post_type->name, [ 'awb_global_options', 'awb_prebuilts', 'awb_studio', 'fusion_tb_layout', 'fusion_icons' ], true ) ) : ?>
											<?php if ( 'slide' !== $post_type->name ) : ?>
											<li class="awb-role-manager-access-item">
												<div class="fusion-builder-option-field">
													<div class="awb-role-manager-item-label"><?php esc_html_e( 'Back-end Builder', 'fusion-builder' ); ?></div>
													<div class="fusion-form-radio-button-set ui-buttonset enable-builder-ui<?php echo esc_attr( $maybe_disabled ); ?>">
														<?php $value = $this->get_option_page_value( $role_name, $post_type->name, 'backed_builder_edit' ); ?>
														<?php $default_access = isset( $default_capabilities[ $role_name ][ $post_type->name ]['backed_builder_edit'] ) ? $default_capabilities[ $role_name ][ $post_type->name ]['backed_builder_edit'] : ''; ?>
														<input type="checkbox" <?php echo $value ? 'checked' : ''; ?> data-default="<?php echo esc_attr( $default_access ); ?>" class="awb-hidden button-set-value" name="role_manager_caps[<?php echo esc_attr( $role_name ); ?>][<?php echo esc_attr( $post_type->name ); ?>][backed_builder_edit]" value="<?php echo esc_attr( $value ); ?>" />
														<a data-value="on" class="ui-button buttonset-item<?php echo ( 'on' === $value ) ? ' ui-state-active' : ''; ?>" href="#"><?php esc_html_e( 'On', 'fusion-builder' ); ?></a>
														<a data-value="off" class="ui-button buttonset-item<?php echo ( 'off' === $value ) ? ' ui-state-active' : ''; ?>" href="#"><?php esc_html_e( 'Off', 'fusion-builder' ); ?></a>
													</div>
												</div>
											</li>
											<li class="awb-role-manager-access-item">
												<div class="fusion-builder-option-field">
													<div class="awb-role-manager-item-label"><?php esc_html_e( 'Live Builder', 'fusion-builder' ); ?></div>
													<div class="fusion-form-radio-button-set ui-buttonset enable-builder-ui<?php echo esc_attr( $maybe_disabled ); ?>">
														<?php $value = $this->get_option_page_value( $role_name, $post_type->name, 'live_builder_edit' ); ?>
														<?php $default_access = isset( $default_capabilities[ $role_name ][ $post_type->name ]['live_builder_edit'] ) ? $default_capabilities[ $role_name ][ $post_type->name ]['live_builder_edit'] : ''; ?>
														<input type="checkbox" <?php echo $value ? 'checked' : ''; ?> data-default="<?php echo esc_attr( $default_access ); ?>" class="awb-hidden button-set-value" name="role_manager_caps[<?php echo esc_attr( $role_name ); ?>][<?php echo esc_attr( $post_type->name ); ?>][live_builder_edit]" value="<?php echo esc_attr( $value ); ?>" />
														<a data-value="on" class="ui-button buttonset-item<?php echo ( 'on' === $value ) ? ' ui-state-active' : ''; ?>" href="#"><?php esc_html_e( 'On', 'fusion-builder' ); ?></a>
														<a data-value="off" class="ui-button buttonset-item<?php echo ( 'off' === $value ) ? ' ui-state-active' : ''; ?>" href="#"><?php esc_html_e( 'Off', 'fusion-builder' ); ?></a>
													</div>
												</div>
											</li>
										<?php endif; ?>
										<li class="awb-role-manager-access-item">
											<div class="fusion-builder-option-field">
												<div class="awb-role-manager-item-label"><?php esc_html_e( 'Page Options', 'fusion-builder' ); ?></div>
												<div class="fusion-form-radio-button-set ui-buttonset enable-builder-ui<?php echo esc_attr( $maybe_disabled ); ?>">
													<?php $value = $this->get_option_page_value( $role_name, $post_type->name, 'page_options' ); ?>
													<?php $default_access = isset( $default_capabilities[ $role_name ][ $post_type->name ]['page_options'] ) ? $default_capabilities[ $role_name ][ $post_type->name ]['page_options'] : ''; ?>
													<input type="checkbox" <?php echo $value ? 'checked' : ''; ?> data-default="<?php echo esc_attr( $default_access ); ?>" class="awb-hidden button-set-value" name="role_manager_caps[<?php echo esc_attr( $role_name ); ?>][<?php echo esc_attr( $post_type->name ); ?>][page_options]" value="<?php echo esc_attr( $value ); ?>" />
													<a data-value="on" class="ui-button buttonset-item<?php echo ( 'on' === $value ) ? ' ui-state-active' : ''; ?>" href="#"><?php esc_html_e( 'On', 'fusion-builder' ); ?></a>
													<a data-value="off" class="ui-button buttonset-item<?php echo ( 'off' === $value ) ? ' ui-state-active' : ''; ?>" href="#"><?php esc_html_e( 'Off', 'fusion-builder' ); ?></a>
												</div>
											</div>
										</li>
											<?php if ( 'fusion_form' === $post_type->name ) : ?>
											<li class="awb-role-manager-access-item awb-form-submissions">
												<div class="fusion-builder-option-field">
													<div class="awb-role-manager-item-label"><?php esc_html_e( 'View Submissions', 'fusion-builder' ); ?></div>
													<div class="fusion-form-radio-button-set ui-buttonset enable-builder-ui">
														<?php $value = $this->get_option_page_value( $role_name, $post_type->name, 'submissions_access' ); ?>
														<?php $default_access = isset( $default_capabilities[ $role_name ][ $post_type->name ]['submissions_access'] ) ? $default_capabilities[ $role_name ][ $post_type->name ]['submissions_access'] : ''; ?>
														<input type="checkbox" <?php echo $value ? 'checked' : ''; ?> data-default="<?php echo esc_attr( $default_access ); ?>" class="awb-hidden button-set-value" name="role_manager_caps[<?php echo esc_attr( $role_name ); ?>][<?php echo esc_attr( $post_type->name ); ?>][submissions_access]" value="<?php echo esc_attr( $value ); ?>" />
														<a data-value="on" class="ui-button buttonset-item<?php echo ( 'on' === $value ) ? ' ui-state-active' : ''; ?>" href="#"><?php esc_html_e( 'On', 'fusion-builder' ); ?></a>
														<a data-value="off" class="ui-button buttonset-item<?php echo ( 'off' === $value ) ? ' ui-state-active' : ''; ?>" href="#"><?php esc_html_e( 'Off', 'fusion-builder' ); ?></a>
													</div>
												</div>
											</li>
										<?php endif; ?>
											<?php if ( 'avada_library' === $post_type->name ) : ?>
											<li class="awb-role-manager-access-item">
												<div class="fusion-builder-option-field">
													<div class="awb-role-manager-item-label"><?php esc_html_e( 'Global Elements', 'fusion-builder' ); ?></div>
													<div class="fusion-form-radio-button-set ui-buttonset enable-builder-ui<?php echo esc_attr( $maybe_disabled ); ?>">
														<?php $value = $this->get_option_page_value( $role_name, $post_type->name, 'global_elements' ); ?>
														<?php $default_access = isset( $default_capabilities[ $role_name ][ $post_type->name ]['global_elements'] ) ? $default_capabilities[ $role_name ][ $post_type->name ]['global_elements'] : ''; ?>
														<input type="checkbox" <?php echo $value ? 'checked' : ''; ?> data-default="<?php echo esc_attr( $default_access ); ?>" class="awb-hidden button-set-value" name="role_manager_caps[<?php echo esc_attr( $role_name ); ?>][<?php echo esc_attr( $post_type->name ); ?>][global_elements]" value="<?php echo esc_attr( $value ); ?>" />
														<a data-value="on" class="ui-button buttonset-item<?php echo ( 'on' === $value ) ? ' ui-state-active' : ''; ?>" href="#"><?php esc_html_e( 'On', 'fusion-builder' ); ?></a>
														<a data-value="off" class="ui-button buttonset-item<?php echo ( 'off' === $value ) ? ' ui-state-active' : ''; ?>" href="#"><?php esc_html_e( 'Off', 'fusion-builder' ); ?></a>
													</div>
												</div>
											</li>
										<?php endif; ?>                                        
										<?php endif; ?>
									</ul>
								</li>
								<?php endforeach; ?>
							</ul>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>