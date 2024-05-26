<?php

namespace cmas\classes\plugins;

class ACF {

	public function __construct() {
		//      https://www.advancedcustomfields.com/resources/options-page/
		add_action( 'init', [ $this, 'register_options_page' ] );

		//      in this hook you need register your fields
		//      https://www.advancedcustomfields.com/resources/register-fields-via-php/
		add_action( 'init', [ $this, 'register_fields' ] );
	}

	public function register_options_page() {

		if ( function_exists( 'acf_add_options_page' ) ) {
			acf_add_options_page(
				[
					'page_title' => esc_attr__( 'Autologin Panda', 'cmtrading-autologin' ),
					'menu_title' => esc_attr__( 'Autologin Panda', 'cmtrading-autologin' ),
					'menu_slug'  => 'cm-autologin-panda-options',
					'capability' => 'edit_posts',
					'icon_url'   => 'dashicons-admin-network', // Add this line and replace the second inverted commas with class of the icon you like
					'position'   => 30,
					'redirect'   => false,
				]
			);
		}
	}

	public function register_fields() {
		if ( function_exists( 'acf_add_local_field_group' ) ) {

			acf_add_local_field_group(
				array(
					'key'                   => 'group_62f20424bf6b0',
					'title'                 => esc_attr__( 'Autologin Panda settings', 'cmtrading-autologin' ),
					'fields'                => array(
						array(
							'key'               => 'field_630b0fb1d56eR',
							'label'             => esc_attr__( 'Enable Autologin', 'cmtrading-autologin' ),
							'name'              => 'cmas_autologin_enable',
							'type'              => 'true_false',
							'instructions'      => '',
							'required'          => 0,
							'conditional_logic' => 0,
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'message'           => '',
							'default_value'     => 0,
							'ui'                => 1,
							'ui_on_text'        => '',
							'ui_off_text'       => '',
						),
						array(
							'key'                 => 'field_gf6T3263db56t',
							'label'               => esc_attr__( 'Matching the lang parameter', 'cmtrading-autologin' ),
							'name'                => 'cmas_matching_lang_list',
							'type'                => 'repeater',
							'instructions'        => '',
							'required'            => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_630b0fb1d56eR',
										'operator' => '==',
										'value'    => '1',
									),
								),
							),
							'wrapper'             => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'wpml_cf_preferences' => 1,
							'collapsed'           => '',
							'min'                 => 0,
							'max'                 => 0,
							'layout'              => 'table',
							'button_label'        => esc_attr__( 'Add Item', 'cmtrading-autologin' ),
							'sub_fields'          => array(
								array(
									'key'               => 'field_62f210ebg3j9L',
									'label'             => esc_attr__( 'The value of the lang parameter', 'cmtrading-autologin' ),
									'name'              => 'param_lang_value',
									'type'              => 'text',
									'instructions'      => '',
									'required'          => 0,
									'conditional_logic' => 0,
									'wrapper'           => array(
										'width' => '',
										'class' => '',
										'id'    => '',
									),
									'default_value'     => '',
									'placeholder'       => '',
									'prepend'           => '',
									'append'            => '',
									'maxlength'         => '',
								),
								array(
									'key'               => 'field_9Jd8f0ec26vrt',
									'label'             => esc_attr__( 'The domain to redirect to', 'cmtrading-autologin' ),
									'name'              => 'param_lang_domain',
									'type'              => 'url',
									'instructions'      => '',
									'required'          => 0,
									'conditional_logic' => 0,
									'wrapper'           => array(
										'width' => '',
										'class' => '',
										'id'    => '',
									),
									'default_value'     => '',
									'placeholder'       => '',
									'prepend'           => '',
									'append'            => '',
									'maxlength'         => '',
								),
							),
						),
						array(
							'key'                 => 'field_5f5e326bmjJf9k',
							'label'               => esc_attr__( 'IP Black List', 'cmtrading-autologin' ),
							'name'                => 'cmas_autologin_ip_black_list',
							'type'                => 'repeater',
							'instructions'        => '',
							'required'            => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_630b0fb1d56eR',
										'operator' => '==',
										'value'    => '1',
									),
								),
							),
							'wrapper'             => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'wpml_cf_preferences' => 1,
							'collapsed'           => '',
							'min'                 => 0,
							'max'                 => 0,
							'layout'              => 'table',
							'button_label'        => esc_attr__( 'Add Address', 'cmtrading-autologin' ),
							'sub_fields'          => array(
								array(
									'key'               => 'field_62f210ecf6Tr9',
									'label'             => esc_attr__( 'IP addresses', 'cmtrading-autologin' ),
									'name'              => 'cmas_autologin_ip',
									'type'              => 'text',
									'instructions'      => '',
									'required'          => 0,
									'conditional_logic' => 0,
									'wrapper'           => array(
										'width' => '',
										'class' => '',
										'id'    => '',
									),
									'default_value'     => '',
									'placeholder'       => '',
									'prepend'           => '',
									'append'            => '',
									'maxlength'         => '',
								),
							),
						),
					),
					'location'              => array(
						array(
							array(
								'param'    => 'options_page',
								'operator' => '==',
								'value'    => 'cm-autologin-panda-options',
							),
						),
					),
					'menu_order'            => 0,
					'position'              => 'normal',
					'style'                 => 'default',
					'label_placement'       => 'top',
					'instruction_placement' => 'label',
					'hide_on_screen'        => '',
					'active'                => true,
					'description'           => '',
					'show_in_rest'          => 0,
				)
			);

		}
	}

}
