<?php

if (!@include_once 'mdsc_deps.php') {
	throw new Exception("Unable to include mdsc_deps.php");
}

class Schedule {
	private static $_js_included = array();
	private static $_modals = array();
	private static $_modals_emitted = array();
	private static $_supported_grid_sizes = array('1', '2', '3', '4');
	private static $_group = 0;

	public static function new_grp_id() {
		self::$_group++;
		return 'g' . self::$_group;
	}

	public static function maybe_esc_html($content) {
		if (empty($content) || $content[0] == '<') {
			return $content;
		}
		return esc_html($content);
	}

	public static function begin_grid($size) {
		assert(in_array($size, self::$_supported_grid_sizes));

		echo HtmlGen::elem(
			'div',
			array('class' => 'schedule_hint')
		);
		echo esc_html('Drag me!') . ' &#8596 ';
		echo esc_html("I'm best in landscape");
		echo HtmlGen::elem(
			'span',
			array('class' => 'portrait_only')
		);
		echo esc_html(' (Turn your phone.)');
		echo '</span></div>';

		echo HtmlGen::elem(
			'div',
			array('class' => "schedule_grid schedule_grid$size")
		);
	}

	public static function end_grid() {
		echo '</div>';
	}

	/*
	 * The header of a schedule grid. Usually indicates the beginning of the
	 * event, such as the main dance or workshops.
	 */
 	public static function header($event, $venue=null, $note=null) {
		$title = $event;

		// Venue information (if venue was provided at all)
		if (!empty($venue)) {
			$venue_data = self::_get_data('venue', $venue);
			$title .= ' at ';

			if (empty($venue_data) || $venue_data['name_tba']) {
				$title .= 'TBA';
			} else {
				self::add_venue_modal($venue);
				$name = $venue_data['name'];
				if (!empty($venue_data['shortname'])) {
					$name = $venue_data['shortname'];
				}
				$title .= self::modal_button_ahref(
					'/venues',
					self::get_modal_id('venue', array($venue))
				);
				$title .= esc_html($name);
				$title .= '</a>';
			}
		}

		// build overall structure.
		$html = HtmlGen::div_wrap($title, 'event');

		if ($note) {
			$html .= HtmlGen::div_wrap($note, 'note');
		}

 		echo HtmlGen::div_wrap($html, 'schedule_header');
 	}

	public static function time(
		$start,
		$end,
		$sub=false
	) {
		$classes = array('grid1col', 'schedule_time');
		if ($sub) {
			$classes[] = 'schedule_subtime';
		}

		if ($end) {
			$time_text = $start . esc_html(' to ') . $end;
		} else if ($start) {
			$time_text = $start;
		} else {
			$time_text = '';
		}

		// need an extra inner div to vertically align things
		$html = HtmlGen::div_wrap($time_text);
		echo HtmlGen::div_wrap($html, $classes);
	}

	public static function performance($id, $span='1') {
		echo HtmlGen::div_wrap(
			esc_html("Special Performance (TBA)"),
			array("schedule_event", self::_span_class($span))
		);
	}

	private static function _get_data($type, $id) {
		// TODO: Handle the case where the type is invalid / not found.
		// It currently throws an exception.
		$all_data = MDSC::instance()->data->get_data($type);
		// TODO: Handle the case where where $id is invalid / not found.
		// It currently returns null, I think.
		return isset($all_data[$id]) ? $all_data[$id] : null;
	}

	private static function _span_class($span) {
		assert(in_array($span, self::$_supported_grid_sizes));
		return "grid{$span}col";
	}

	private static function _workshop_attrs($group, $id) {
		return array(
			'data-workshop-group' => $group,
			'data-workshop' => $id,
		);
	}

	public static function workshop($group, $id, $span='1') {
		$data = self::_get_data('class', $id);
		$html = '';

		$tba = $data['title_tba'] || empty($data['title']);

		// Workshop title
		if ($tba) {
			$html .= HtmlGen::div_wrap(
				esc_html('Class TBA'),
				array('workshop_title', 'tba')
			);
		} else {
			$html .= HtmlGen::div_wrap(
				esc_html($data['title']),
				'workshop_title'
			);
		}

	 	if (! $tba && ! empty($data['teacher_text'])) {
			$html .= HtmlGen::div_wrap(
				esc_html($data['teacher_text']),
				'workshop_teacher'
			);
		}

		if (! $tba && ! empty($data['tags'])) {
			$html .= HtmlGen::div_wrap(
				self::tags_html($data['tags'], true),
				'workshop_tags'
			);
		}

		if ($tba || empty($data['description'])) {
			$html .= HtmlGen::div_wrap(
				esc_html('Description TBA'),
				array('workshop_description', 'tbd')
			);
		} else {
			$html .= HtmlGen::div_wrap(
				/* richtext */ $data['description'],
				'workshop_description'
			);
		}

		$html .= HtmlGen::div_wrap('', 'tv_style_shader');

		$classes = array(
			"schedule_event",
			self::_span_class($span),
			"tv_button",
			"tv_stylize"
		);

		echo HtmlGen::elem(
			'div',
			array_merge(
				array('class' => HtmlGen::classes($classes)),
				self::_workshop_attrs($group, $id)
			)
		);
		echo $html;
		echo '</div>';
	}

	/**
	 * Generate HTML for the provided workshop tags.
	 *
	 * $short [boolean] If true, these tags are shown on the top level of the
	 * workshop schedule. If false, this is within the details.
	 */
	private static function tags_html($tags, $short) {
		$tag_values = explode(',', $tags);
		$tag_data = array();
		foreach ($tag_values as $tv) {
			$tv = trim($tv);
			if (empty($tv)) {
				continue;
			}
			$d = self::_get_data('tags', $tv);
			if (empty($d)) {
				$d = array(
					'id' => $tv,
					'missing' => true
				);
			}
			$tag_data[] = $d;
		}

		$first = true;
		$html = '';
		foreach ($tag_data as $td) {
			if (!$first) {
				$html .= ', ';
			}
			$first = false;

			if (isset($td['missing'])) {
				$html .= HtmlGen::elem(
					'span',
					array(
						'style' => 'color:red;'
					)
				);
				$html .= esc_html($td['id']);
				$html .= '</span>';
			} elseif ($short) {
				$shortname = $td['name'];
				if (! empty($td['shortname'])) {
					$shortname = $td['shortname'];
				}
				$html .= esc_html($shortname);
			} else {
				self::add_tag_modal($td['id']);
				$html .= self::modal_button_ahref(
					'/workshops',
					self::get_modal_id('tag', array($td['id']))
				);
				$html .= esc_html($td['name']);
				$html .= '</a>';
			}

			$first = false;
		}

		return $html;
	}

	public static function workshop_details($group, $id) {
		$data = self::_get_data('class', $id);
		$html = '';

		$tba = $data['title_tba'] || empty($data['title']);

		// Workshop title
		if ($tba) {
			$html .= HtmlGen::div_wrap(
				esc_html('Class TBA'),
				array('workshop_title', 'tba')
			);
		} else {
			$html .= HtmlGen::div_wrap(
				esc_html($data['title']),
				'workshop_title', 'astyle'
			);
		}

		$html .= esc_html(' with ');

		if (! $tba && ! empty($data['teacher_text'])) {
			$html .= HtmlGen::elem(
				'div',
				array('class' => 'workshop_teacher')
			);

			$teachers = array();
			if (!empty($data['teacher_tags'])) {
				$tt_values = explode(',', $data['teacher_tags']);
				foreach ($tt_values as $tt) {
					$tt = trim($tt);
					if (!empty($tt)) {
						$teachers[] = $tt;
					}
				}
			}

			if (empty($teachers)) {
				$html .= $data['teacher_text'];
			} else {
				self::add_bio_modal($teachers);
				$html .= self::modal_button_ahref(
					'/instructors',
					self::get_modal_id('bio', $teachers)
				);
				$html .= $data['teacher_text'];
				$html .= '</a>';
			}
			$html .= '</div>';
		}

		$html = HtmlGen::div_wrap($html, 'workshop_details_top');

		if ($tba || empty($data['description'])) {
			$html .= HtmlGen::div_wrap(
				esc_html('Description TBA'),
				array('workshop_description', 'tbd')
			);
		} else {
			$html .= HtmlGen::div_wrap(
				/* richtext */ $data['description'],
				'workshop_description'
			);
		}

		if (! $tba && ! empty($data['tags'])) {
			$html .= HtmlGen::div_wrap(
				self::tags_html($data['tags'], false),
				'workshop_tags'
			);
		}

		$html .= HtmlGen::div_wrap(
			esc_html('[close]'),
			array('close_button', 'tv_button')
		);

		$classes = array(
			"workshop_details",
			"gridspan",
			"tv_toggle_vis",
			"sync_scroll",
		);
		echo HtmlGen::elem(
			'div',
			array_merge(
				array('class' => HtmlGen::classes($classes)),
				self::_workshop_attrs($group, $id)
			)
		);
		echo $html;
		echo '</div>';
	}

	private static function _local_path_url($local_path, $version=true) {
		$url = get_stylesheet_directory_uri() . '/' . $local_path;
		if ($version) {
			return add_query_arg('v', wp_get_theme()->version, $url);
		}
		else {
			return $url;
		}
	}

	public static function js_include_once($local_path) {
		if (in_array($local_path, self::$_js_included)) {
			return false;
		}

		self::$_js_included[] = $local_path;

		echo HtmlGen::eelem(
			'script',
			array(
				'type' => 'text/javascript',
				'src' => self::_local_path_url($local_path)
			)
		);

		return true;
	}

	public static function emit_new_modals() {
		foreach (self::$_modals as $modal_key => $html) {
			if (in_array($modal_key, self::$_modals_emitted)) {
				continue;
			}
			self::$_modals_emitted[] = $modal_key;

			$id = 'modal-' . $modal_key;

			self::emit_modal($html, $id);
		}
	}

	private static function emit_modal($html, $id) {
		echo HtmlGen::elem(
			'div',
			array(
				'class' => 'modal',
				'id' => $id,
			)
		);

		echo HtmlGen::elem(
			'div',
			array('class' => 'modal-wrapper')
		);

		echo HtmlGen::div_wrap(
			'<span class="close">&times;</span>' . $html,
			'modal-content'
		);

		echo '</div></div>';
	}

	/**
	 * Splits up markup into logical elements. Each element starts with a '#'
	 * character at the beginning of a line. Any subsequent lines are appended to
	 * the existing (previous) section. The only way to fail to parse is to have
	 * non-empty content at the start of the markup block without a leading '#'.
	 * @param string $content A block of text that is [smb_schedule] markup.
	 * @return array(string) Each section of markup, unaltered.
	 */
	public static function split_sections($content) {
		$lines = preg_split('/(\r\n?|\n)/', $content);

		$scount = 0;
		$sections = array();
		foreach ($lines as $line) {
			$firstchar = isset($line[0]) ? $line[0] : null;
			if ($firstchar == '#') {
				$sections[] = $line;
				$scount++;
			} elseif ($scount >= 1) {
				$sections[$scount-1] = $sections[$scount-1] . "\n" . $line;
			} elseif (trim($line) != '') {
				echo sweetmo_internal_error_html('Improper schedule markup');
				return array();
			}
		}

		return $sections;
	}

	/**
	 * The main entry point for processing of schedule markup.
	 *
	 * @param  array(string) $sections Output from `split_sections`
	 * @return boolean Success
	 */
	public static function handle_sections($sections) {
		$group = self::new_grp_id();
		foreach ($sections as $section) {

			// Second character. (The first character is always '#')
			$sc = $section[1];
			if ($sc == '@') {
				$group = self::new_grp_id();
				self::handle_time_section($section);
			} elseif ($sc == '#') {
				$group = self::new_grp_id();
				self::handle_header_section($section);
			} elseif ($sc == ';') {
				// pass. It's a comment.
			} elseif ($sc == 'c' && strncmp($section, '#classes', 8) == 0) {
				self::handle_classes_section($section, $group);
			} elseif ($sc == 'b' && strncmp($section, '#band', 5) == 0) {
				self::handle_band_section($section);
			} elseif ($sc == 'd' && strncmp($section, '#dj', 3) == 0) {
				self::handle_dj_section($section);
			} elseif ($sc == 'p' && strncmp($section, '#performance', 12) == 0) {
				self::handle_performance_section($section);
			} elseif ($sc == 'e' && strncmp($section, '#event', 6) == 0) {
				self::handle_event_section($section);
			} else {
				echo sweetmo_internal_error_html('Unknown section');
				return false;
			}

		}
		return true;
	}

	public static function handle_header_section($section) {
		// remove '##' from leading part and trim
		$section = trim(substr($section, 2));

		$title = $loc = $note = null;
		$header = explode('@', $section, 2);
		$title = self::maybe_esc_html(trim($header[0]));
		if (! empty($header[1])) {
			$header = explode(',', $header[1], 2);
			$loc = trim($header[0]);
			if (count($header) > 1) {
				$note = self::maybe_esc_html(trim($header[1]));
			}
		}

		self::header($title, $loc, $note);
	}

	public static function handle_time_section($section) {
		// remove '#@'
		$section = substr($section, 2);

		$times = explode('-', $section, 2);
		$start = trim($times[0]);
		$end = null;
		if (count($times) > 1) {
			$end = trim($times[1]);
		}

		$subtime = false;
		if ($start[0] == '>') {
			$start = substr($start, 1);
			$subtime = true;
		}

		$start = self::maybe_esc_html($start);
		$end = self::maybe_esc_html($end);
		self::time($start, $end, $subtime);
	}

	public static function handle_classes_section($section, $group) {
		// remove '#classes'
		$section = trim(substr($section, 8));
		$span = '1';

		if ($section[0] == '[') {
			$parts = explode(']', $section, 2);
			if (count($parts) > 1) {
				$span = substr($parts[0], 1);
				$section = trim($parts[1]);
			}
		}

		$classes = explode(',', $section);

		foreach ($classes as $idx => $class) {
			$classes[$idx] = $class = trim($class);
			self::workshop($group, $class, $span);
		}

		foreach ($classes as $class) {
			self::workshop_details($group, $class);
		}
	}

	public static function handle_event_section($section) {
		// remove '#event'
		$section = trim(substr($section, 6));
		$span = '1';

		if ($section[0] == '[') {
			$parts = explode(']', $section);
			if (count($parts) > 1) {
				$span = substr($parts[0], 1);
				$section = trim($parts[1]);
			}
		}

		echo HtmlGen::div_wrap(
			self::maybe_esc_html($section),
			array("schedule_event", self::_span_class($span))
		);
	}

	private static function modal_button_ahref($href, $id) {
		return HtmlGen::elem(
			'a',
			array(
				'class' => 'modal_button',
				'href' => $href,
				'data-modal-id' => "modal-$id"
			)
		);
	}

	private static function add_tag_modal($id) {
		$mid = self::get_modal_id('tag', array($id));

		if (isset(self::$_modals[$mid])) {
			return;
		}

		self::$_modals[$mid] = sweetmo_sc_smb_tag(
			array(),
			$id,
			null
		);
	}

	private static function add_venue_modal($id) {
		$mid = self::get_modal_id('venue', array($id));

		if (isset(self::$_modals[$mid])) {
			return;
		}

		self::$_modals[$mid] = sweetmo_sc_smb_venue(
			array(),
			$id,
			null
		);
	}

	private static function get_modal_id($type, $ids) {
		$mid = '';
		foreach ($ids as $id) {
			$mid .= '_' . $id;
		}
		return $mid;
	}

	/* $ids can be either a scalar (string ID) or array (array of String IDs) */
	private static function add_bio_modal($ids) {
		if (!is_array($ids)) {
			$ids = array($ids);
		}

		$mid = self::get_modal_id('bio', $ids);

		if (isset(self::$_modals[$mid])) {
			return;
		}

		$content = '';
		$first = true;

		foreach ($ids as $id) {
			if (! $first) {
				$content .= '<hr/>';
			}
			$first = false;
			$content .= sweetmo_sc_smb_bio(
				array(),
				$id,
				null
			);
		}

		self::$_modals[$mid] = $content;
	}

	// handles '#band[X] ...' markup (the [X] is optional)
	public static function handle_band_section($section) {
		//
		// remove '#band' and look for [$span]
		//
		$section = trim(substr($section, 5));
		$span = '1';

		if ($section[0] == '[') {
			$parts = explode(']', $section);
			if (count($parts) > 1) {
				$span = substr($parts[0], 1);
				$section = trim($parts[1]);
			}
		}

		//
		// parse $section for '{$band}/{$dj} {$title}'
		// only the band is required.
		//
		$parts = preg_split('/\s+/', $section, 2);
		$band_dj = trim($parts[0]);
		$title = isset($parts[1]) ? $parts[1] : null;

		$parts = explode('/', $band_dj, 2);
		$band = $parts[0];
		$dj = isset($parts[1]) ? $parts[1] : null;

		// defaults...
		if (empty($title)) {
			$title = 'Live Music';
		}

		$band_data = self::_get_data('person', $band);
		$dj_data = null;
		if (!empty($dj)) {
			$dj_data = self::_get_data('person', $dj);
		}

		//
		// build the content out of $title, $band_data, and $dj_data
		//
		$content = '';
		if (empty($band_data) || empty($band_data['name'])) {
			$content .= '<b>' . esc_html('Band TBA') . '</b>';
		} else {
			self::add_bio_modal($band);
			$content .= '<b>';
			$content .= self::modal_button_ahref(
				'/music',
				self::get_modal_id('bio', array($band))
			);
			$content .= esc_html($band_data['name']);
			$content .= '</a></b>';
		}

		if (!empty($title)) {
			$content .= '<br/>';
			$content .= self::maybe_esc_html(trim($title));
		}

		if (!empty($dj_data) && !empty($dj_data['name'])) {
			self::add_bio_modal($dj);

			$content .= HtmlGen::elem(
				'div',
				array('class' => 'cblock')
			);
			$content .= esc_html("Set breaks DJ'd by ");
			$content .= self::modal_button_ahref(
				'/music',
				self::get_modal_id('bio', array($dj))
			);

			$name = $dj_data['name'];
			if (!empty($dj_data['shortname'])) {
				$name = $dj_data['shortname'];
			}
			$content .= esc_html($name);

			$content .= '</a>';
			$content .= "</div>";
		}

		//
		// emit!
		//
		echo HtmlGen::div_wrap(
			$content,
			array("schedule_event", self::_span_class($span))
		);
}

	// handles '#dj[X] ...' markup (the [X] is optional)
	public static function handle_dj_section($section) {
		//
		// remove '#dj' and look for [$span]
		//
		$section = trim(substr($section, 3));
		$span = '1';

		if ($section[0] == '[') {
			$parts = explode(']', $section);
			if (count($parts) > 1) {
				$span = substr($parts[0], 1);
				$section = trim($parts[1]);
			}
		}

		//
		// parse $section for '{$dj} {$title}'
		// only the band is required.
		//
		$parts = preg_split('/\s+/', $section, 2);
		$dj = trim($parts[0]);
		$description = isset($parts[1]) ? $parts[1] : null;

		$dj_data = self::_get_data('person', $dj);

		//
		// build the content out of $title, $band_data, and $dj_data
		//
		$content = '';
		if (empty($dj_data) || empty($dj_data['name'])) {
			$content .= esc_html("DJ'd music by TBA");
		} else {
			self::add_bio_modal($dj);
			$content .= HtmlGen::elem(
				'div',
				array('class' => 'cblock')
			);
			$content .= esc_html("DJ'd music by ");
			$content .= self::modal_button_ahref(
				'/music',
				self::get_modal_id('bio', array($dj))
			);
			$name = $dj_data['name'];
			if (!empty($dj_data['shortname'])) {
				$name = $dj_data['shortname'];
			}
			$content .= esc_html($name);
			$content .= '</a></div>';
		}

		if (!empty($description)) {
			$content .= HtmlGen::elem(
				'div',
				array('class' => 'cblock')
			);
			$content .= self::maybe_esc_html(trim($description));
			$content .= '</div>';
		}

		//
		// emit!
		//
		echo HtmlGen::div_wrap(
			$content,
			array("schedule_event", self::_span_class($span))
		);
	}

	public static function handle_performance_section($section) {
		// remove '#performance'
		$section = trim(substr($section, 12));
		$span = '1';

		if ($section[0] == '[') {
			$parts = explode(']', $section);
			if (count($parts) > 1) {
				$span = substr($parts[0], 1);
				$section = trim($parts[1]);
			}
		}

		self::performance($section, $span);
	}
}

1;
