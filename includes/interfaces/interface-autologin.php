<?php

namespace cmas\interfaces;

interface Autologin {

	public function get_autologin_link( $param ): ?string;

}
