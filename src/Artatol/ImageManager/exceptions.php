<?php

/**
 * This file is part of the Artatol (http://www.artatol.cz)
 * Copyright (c) 2014 Martin Charouzek (martin@charouzkovi.cz)
 */

namespace Artatol\ImageManager;

interface Exception {
	
}

class NotValidImageException extends \RuntimeException implements Exception {
	
}

class NotFoundException extends \RuntimeException implements Exception {
	
}
