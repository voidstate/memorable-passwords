<?php
/*
 * Class to generate a password easily memorable by humans. Place names have a UK bias.
 */

namespace voidstate;

class MemorablePassword
{

	protected $_num_digits;
	protected $_capitalise_mode;
	protected $_wordlist_mode;

	const CAPITALISE_MODE_NONE = 'none';
	const CAPITALISE_MODE_ONE = 'one';
	const CAPITALISE_MODE_SOME = 'some';

	const WORDLIST_MODE_ALL = 'all';
	const WORDLIST_MODE_ABSTRACT_WORDS_ONLY = 'abstract_words_only';
	const WORDLIST_MODE_ONLY_UK = 'uk_only';
	const WORDLIST_MODE_ONLY_WORLDWIDE = 'worldwide_only';
	const WORDLIST_MODE_ALL_LOCATIONS = 'all_locations';
	const WORDLIST_MODE_NOT_ABSTRACT = 'not_abstract';

	public function __construct( $num_digits = 3, $capitalise_mode = self::CAPITALISE_MODE_NONE, $wordlist_mode = self::WORDLIST_MODE_ABSTRACT_WORDS_ONLY )
	{
		$this->_num_digits = $num_digits;
		$this->_capitalise_mode = $capitalise_mode;
		$this->_wordlist_mode = $wordlist_mode;

		// seed random number generator
		mt_srand( ( float)microtime() * 1000000 );
	}

	/**
	 * Get a password, according to the current rules
	 *
	 * @return string
	 */
	public function generate()
	{
		$secure_password = '';
		$word = $this->getWord();
		$char_array = preg_split( '//', $word, -1, PREG_SPLIT_NO_EMPTY );
		$num = $this->_getNumber();

		// split word and add in number
		$randomPos = mt_rand( 1, count( $char_array ) - 1 );
		for( $i = 0; $i < count( $char_array ); $i++ )
		{
			if( $i == $randomPos )
			{
				$secure_password .= $num;
			}
			$secure_password .= $char_array[ $i ];
		}

		// return
		return $secure_password;
	}

	/**
	 * Change the mode used to select how much of the word is capitalised
	 *
	 * @param $capitalise_mode
	 */
	public function setCapitaliseMode( $capitalise_mode )
	{
		$this->_capitalise_mode = $capitalise_mode;
	}

	/**
	 * Change the mode used to select the word element
	 *
	 * @param const $wordlist_mode
	 */
	public function setWordlistMode( $wordlist_mode )
	{
		$this->_wordlist_mode = $wordlist_mode;
	}

	/**
	 * Get the numeric element of the password
	 *
	 * @return string
	 */
	protected function _getNumber()
	{
		// generate
		$number = mt_rand( 0, pow( 10, $this->_num_digits ) - 1 );

		// pad
		$number = str_pad( $number, $this->_num_digits, '0', STR_PAD_LEFT );

		// replace 0s and 1s to avoid confusion
		$char_array = preg_split( '//', $number, -1, PREG_SPLIT_NO_EMPTY );
		for( $i = 0; $i < count( $char_array ); $i++ )
		{
			if( $char_array[ $i ] == '0' || $char_array[ $i ] == '1' )
			{
				$char_array[ $i ] = mt_rand( 2, 9 );
			}
		}

		$number = implode( '', $char_array );

		return (string)$number;
	}

	/**
	 * Return a single word (used for external building of passwords)
	 *
	 * @param bool $character_limit
	 *
	 * @return string
	 */
	function getWord( $character_limit = false )
	{
		// Which word list? 0, 1 or 2 represents 3 lists
		if( $this->_wordlist_mode == self::WORDLIST_MODE_ABSTRACT_WORDS_ONLY )
		{
			$wordlist = 0;
		}
		else if( $this->_wordlist_mode == self::WORDLIST_MODE_ALL )
		{
			$wordlist = rand( 0, 1 ) ? 0 : rand( 1, 2 ); // weight towards safe words
		}
		else if( $this->_wordlist_mode == self::WORDLIST_MODE_ALL_LOCATIONS )
		{
			$wordlist = rand( 1, 2 );
		}
		else if( $this->_wordlist_mode == self::WORDLIST_MODE_ONLY_UK )
		{
			$wordlist = 1;
		}
		else if( $this->_wordlist_mode == self::WORDLIST_MODE_ONLY_WORLDWIDE )
		{
			$wordlist = 2;
		}
		else if( $this->_wordlist_mode == self::WORDLIST_MODE_NOT_ABSTRACT )
		{
			$wordlist = rand( 1, 2 );
		}

		// load words
		if( $wordlist == 0 )
		{
			$words = $this->_getSafeWords();
		}
		else if( $wordlist == 1 )
		{
			$words = $this->_getUkLocations();
		}
		else if( $wordlist == 2 )
		{
			$words = $this->_getWorldwideLocations();
		}

		$attempts = 0;
		while( $attempts < 50 )
		{
			$word = $words[ mt_rand( 0, count( $words ) - 1 ) ];
			if( strlen( $word ) <= $character_limit || $character_limit == false )
			{
				return $this->_capitalise( $word );
			}
			$attempts++;
		}

		// none found so create random string
		$word = '';
		for( $i = 0; $i < 4; $i++ )
		{
			$word .= chr( rand( 97, 122 ) ); // numbers of the ascii table (small-caps)
		}

		return $this->_capitalise( $word );
	}

	/**
	 * Capitalise word based on current rules (set using setCapitaliseMode)
	 *
	 * @param $word
	 *
	 * @return string
	 * @throws \Exception
	 */
	protected function _capitalise( $word )
	{
		if( $this->_capitalise_mode == self::CAPITALISE_MODE_NONE )
		{
			return $word;
		}
		else if( $this->_capitalise_mode == self::CAPITALISE_MODE_ONE )
		{
			return $this->_capitaliseOne( $word );
		}
		else if( $this->_capitalise_mode == self::CAPITALISE_MODE_SOME )
		{
			return $this->_capitaliseSome( $word );
		}
		else
		{
			throw new \InvalidArgumentException( 'Capitalise mode not recognised' );
		}
	}

	/**
	 * Capitalise one letters
	 *
	 * @param string $word
	 *
	 * @return string
	 */
	protected function _capitaliseOne( $word )
	{
		$char_array = preg_split( '//', $word, -1, PREG_SPLIT_NO_EMPTY );
		$random_character = mt_rand( 0, count( $char_array ) - 1 );
		$char_array[ $random_character ] = strtoupper( $char_array[ $random_character ] );
		$word = implode( '', $char_array );

		// return
		return $word;
	}

	/**
	 * Capitalise a random quarter of all letters
	 *
	 * @param string $word
	 *
	 * @return string
	 */
	protected function _capitaliseSome( $word )
	{
		$char_array = preg_split( '//', $word, -1, PREG_SPLIT_NO_EMPTY );

		for( $i = 0; $i < count( $char_array ); $i++ )
		{
			$random_number = mt_rand( 0, 3 );
			if( $random_number == 0 ) // capitalise 1 in 4
			{
				$char_array[ $i ] = strtoupper( $char_array[ $i ] );
			}
		}

		$word = implode( '', $char_array );

		// return
		return $word;
	}

	/**
	 * 1000ish most common words in English with unsuitable ones (death, rape, suicide, etc.) removed (some American spellings unfortunately)
	 *
	 * @return array
	 */
	protected function _getSafeWords()
	{
		return [ 'able', 'about', 'above', 'acacia', 'accept', 'across', 'actor', 'admit', 'advise', 'after', 'again', 'agency', 'agree', 'agriculture', 'aeroplane', 'airport', 'alive', 'almost', 'alone', 'along', 'already', 'also', 'although', 'always', 'amend', 'among', 'amount', 'anaconda', 'ancient', 'animal', 'anniversary', 'announce', 'another', 'answer', 'any', 'apologize', 'appeal', 'appear', 'appoint', 'approve', 'area', 'argue', 'around', 'arrive', 'art', 'ash', 'assist', 'astronaut', 'atmosphere', 'atom', 'automobile', 'autumn', 'award', 'away', 'back', 'badger', 'balance', 'ball', 'balloon', 'ballot', 'bank', 'base', 'beach', 'beauty', 'because', 'begin', 'behind', 'believe', 'bell', 'belong', 'below', 'best', 'between', 'bill', 'bird', 'blanket', 'blind', 'block', 'blue', 'boat', 'book', 'border', 'borrow', 'both', 'bottle', 'bottom', 'brain', 'brave', 'bread', 'break', 'breathe', 'bridge', 'brief', 'bright', 'bring', 'broadcast', 'brown', 'build', 'business', 'busy', 'buttercup', 'cabbage', 'cabinet', 'call', 'calm', 'camel', 'camera', 'campaign', 'cancel', 'candidate', 'cannon', 'capital', 'capture', 'care', 'careful', 'carry', 'case', 'cat', 'catch', 'cattle', 'cause', 'ceasefire', 'celebrate', 'cell', 'center', 'century', 'ceremony', 'chairman', 'champion', 'chance', 'change', 'charge', 'chase', 'chemicals', 'chief', 'circle', 'civil', 'clear', 'clergy', 'climb', 'clock', 'close', 'cloth', 'clothes', 'cloud', 'coal', 'coalition', 'coast', 'cobra', 'coffee', 'cold', 'collect', 'comedy', 'command', 'comment', 'committee', 'common', 'communicate', 'company', 'complete', 'computer', 'concern', 'condition', 'conference', 'confirm', 'conflict', 'congratulate', 'congress', 'connect', 'conservative', 'consider', 'contain', 'continent', 'continue', 'convention', 'cook', 'cool', 'cooperate', 'coot', 'copy', 'correct', 'cost', 'cotton', 'count', 'country', 'cover', 'cow', 'create', 'creature', 'credit', 'crew', 'crops', 'cross', 'crowd', 'cthulhu', 'culture', 'cure', 'current', 'custom', 'dachshund', 'dam', 'dance', 'date', 'deaf', 'deal', 'debate', 'decide', 'declare', 'deep', 'deer', 'defenestration', 'degree', 'delay', 'delegate', 'demand', 'demonstrate', 'depend', 'deplore', 'deploy', 'describe', 'desert', 'design', 'desire', 'details', 'develop', 'device', 'different', 'difficult', 'dig', 'dinner', 'diplomat', 'direct', 'direction', 'disappear', 'discover', 'discuss', 'distance', 'distant', 'dive', 'divide', 'doctor', 'document', 'dollar', 'dolphin', 'door', 'down', 'draft', 'dragon', 'dream', 'dry', 'duty', 'each', 'early', 'earn', 'earth', 'ease', 'east', 'easy', 'eat', 'economy', 'eagle', 'edge', 'educate', 'effect', 'effort', 'eggtimer', 'either', 'elect', 'electricity', 'electron', 'element', 'elephant', 'embassy', 'emotion', 'employ', 'empty', 'energy', 'engine', 'engineer', 'enjoy', 'enough', 'enter', 'equipment', 'equal', 'escape', 'especially', 'even', 'event', 'ever', 'every', 'evidence', 'evolution', 'exact', 'example', 'excellent', 'except', 'exchange', 'excite', 'excuse', 'exist', 'expand', 'expect', 'experiment', 'expert', 'explain', 'explore', 'export', 'express', 'extend', 'extra', 'face', 'fact', 'fairy', 'falcon', 'fall', 'famous', 'farm', 'fast', 'federal', 'feed', 'feel', 'field', 'fierce', 'film', 'final', 'find', 'fine', 'finish', 'firm', 'first', 'fish', 'flag', 'flat', 'float', 'floor', 'flow', 'flower', 'fluid', 'follow', 'food', 'foot', 'for', 'form', 'former', 'forward', 'free', 'freeze', 'fresh', 'friend', 'from', 'front', 'fruit', 'fuel', 'future', 'gain', 'game', 'gather', 'gentle', 'gerbil', 'gift', 'giraffe', 'girlishness', 'give', 'glass', 'goal', 'gold', 'good', 'goodbye', 'goods', 'goose', 'govern', 'government', 'grain', 'grass', 'gray', 'great', 'green', 'grind', 'ground', 'group', 'grow', 'guarantee', 'guard', 'guide', 'guineapig', 'hair', 'half', 'halt', 'happen', 'happy', 'harbour', 'hard', 'head', 'headquarters', 'health', 'hear', 'heart', 'heat', 'heavy', 'helicopter', 'here', 'hero', 'heron', 'hide', 'high', 'hill', 'hippo', 'hold', 'holiday', 'holy', 'home', 'honest', 'honey', 'honour', 'hope', 'horse', 'hospital', 'hot', 'hotel', 'hour', 'house', 'how', 'however', 'huge', 'humour', 'hurry', 'ice', 'idea', 'iguana', 'imagine', 'immediate', 'import', 'important', 'include', 'increase', 'independent', 'industry', 'inflation', 'inform', 'insect', 'inspect', 'instead', 'instrument', 'intelligent', 'intense', 'interest', 'international', 'internet', 'invade', 'invent', 'invite', 'involve', 'iron', 'island', 'issue', 'jewel', 'join', 'joint', 'joke', 'judge', 'jump', 'jungle', 'just', 'kangaroo', 'keep', 'kind', 'know', 'koala', 'labour', 'laboratory', 'lake', 'land', 'language', 'large', 'last', 'late', 'laugh', 'launch', 'lead', 'leak', 'learn', 'leave', 'left', 'legal', 'lend', 'leopard', 'less', 'letter', 'level', 'liberal', 'life', 'light', 'lightning', 'like', 'limit', 'line', 'link', 'lion', 'liquid', 'list', 'listen', 'live', 'lizard', 'load', 'local', 'loyal', 'luck', 'machine', 'mail', 'main', 'major', 'majority', 'make', 'maple', 'march', 'mark', 'marker', 'mass', 'material', 'may', 'mayor', 'measure', 'medicine', 'meet', 'melt', 'memorial', 'memory', 'message', 'metal', 'method', 'microscope', 'middle', 'mind', 'mine', 'mineral', 'minister', 'minute', 'miss', 'missing', 'moderate', 'modern', 'money', 'mongoose', 'month', 'moon', 'more', 'morning', 'most', 'motion', 'mountain', 'move', 'much', 'music', 'musical', 'mustard', 'mystery', 'name', 'near', 'necessary', 'neither', 'nerve', 'neutral', 'new', 'news', 'newt', 'next', 'night', 'noise', 'nominate', 'noon', 'normal', 'north', 'not', 'note', 'nothing', 'now', 'nowhere', 'number', 'nurse', 'oak', 'object', 'observe', 'ocean', 'off', 'offer', 'often', 'once', 'operate', 'opinion', 'opposite', 'orbit', 'orchestra', 'order', 'other', 'ostrich', 'over', 'paint', 'palace', 'pamphlet', 'paper', 'parachute', 'parade', 'part', 'party', 'passenger', 'passport', 'past', 'path', 'penguin', 'percent', 'perfect', 'perhaps', 'period', 'permanent', 'permit', 'person', 'physics', 'piano', 'picture', 'piece', 'pilot', 'pipe', 'pirate', 'place', 'planet', 'plant', 'play', 'please', 'plenty', 'plot', 'poem', 'point', 'popular', 'port', 'position', 'possible', 'postpone', 'pour', 'prepare', 'present', 'president', 'press', 'prevent', 'price', 'priest', 'private', 'prize', 'probably', 'produce', 'professor', 'program', 'progress', 'project', 'promise', 'propaganda', 'property', 'propose', 'prosperity', 'protect', 'proud', 'prove', 'provide', 'public', 'publication', 'publish', 'puffin', 'pull', 'pump', 'purchase', 'pure', 'purpose', 'push', 'put', 'python', 'question', 'quick', 'quiet', 'radar', 'radio', 'railroad', 'rain', 'rapid', 'rare', 'rate', 'reach', 'read', 'ready', 'real', 'realistic', 'reason', 'reasonable', 'recognize', 'record', 'red', 'reduce', 'relations', 'release', 'remain', 'remember', 'repair', 'report', 'request', 'resolution', 'responsible', 'rest', 'restrict', 'result', 'retire', 'return', 'rice', 'rich', 'ride', 'right', 'rise', 'river', 'road', 'rock', 'rocket', 'roll', 'room', 'root', 'rope', 'rough', 'round', 'rubber', 'safe', 'sail', 'salt', 'same', 'satellite', 'school', 'science', 'sealion', 'search', 'season', 'seat', 'second', 'security', 'see', 'seek', 'seem', 'seize', 'self', 'senate', 'send', 'sense', 'sentence', 'separate', 'serendipity', 'series', 'serif', 'serious', 'settle', 'several', 'severe', 'shake', 'shape', 'share', 'sharp', 'she', 'shine', 'ship', 'shock', 'shoe', 'short', 'should', 'shout', 'show', 'shrink', 'shut', 'side', 'sign', 'signal', 'silence', 'silver', 'similar', 'simple', 'since', 'sing', 'sink', 'sit', 'size', 'skeleton', 'skill', 'sleep', 'slide', 'slow', 'small', 'smell', 'smile', 'smoke', 'smooth', 'snake', 'snow', 'social', 'soft', 'solid', 'solve', 'some', 'soon', 'sort', 'sound', 'south', 'space', 'speak', 'special', 'speed', 'spend', 'spill', 'spilt', 'spirit', 'split', 'sport', 'sports', 'spread', 'spring', 'spy', 'stamp', 'stand', 'star', 'start', 'starve', 'state', 'station', 'statue', 'stay', 'steal', 'steam', 'steel', 'step', 'stick', 'still', 'stomach', 'stone', 'stop', 'store', 'storm', 'story', 'stove', 'straight', 'strange', 'street', 'stretch', 'strike', 'strong', 'struggle', 'stubborn', 'study', 'submarine', 'substance', 'substitute', 'subversion', 'succeed', 'such', 'sudden', 'suffer', 'sugar', 'summer', 'sun', 'supply', 'support', 'suppose', 'sure', 'surplus', 'surprise', 'surrender', 'surround', 'swan', 'sweet', 'swim', 'sycamore', 'sympathy', 'system', 'take', 'talk', 'tall', 'tank', 'task', 'taste', 'teach', 'team', 'tear', 'tears', 'technical', 'telephone', 'telescope', 'television', 'tell', 'temperature', 'temporary', 'tense', 'term', 'territory', 'test', 'textiles', 'than', 'thank', 'that', 'the', 'theatre', 'then', 'there', 'thick', 'thin', 'thing', 'third', 'this', 'through', 'throw', 'tie', 'tiger', 'time', 'tired', 'tissue', 'today', 'together', 'tomorrow', 'tonight', 'tool', 'toward', 'town', 'trade', 'tradition', 'train', 'transport', 'trap', 'travel', 'treasure', 'treat', 'treaty', 'tree', 'tribe', 'trick', 'trip', 'truck', 'try', 'turn', 'under', 'understand', 'universe', 'university', 'unless', 'until', 'usual', 'valley', 'value', 'vehicle', 'version', 'village', 'violin', 'visit', 'voice', 'volcano', 'vote', 'voyage', 'wages', 'wait', 'walk', 'wall', 'want', 'warm', 'warn', 'wash', 'watch', 'water', 'wave', 'wealth', 'wear', 'weather', 'website', 'weigh', 'welcome', 'well', 'west', 'what', 'wheat', 'wheel', 'when', 'where', 'which', 'while', 'white', 'wide', 'wild', 'will', 'wind', 'window', 'wire', 'wise', 'wish', 'with', 'wonder', 'wood', 'woods', 'word', 'work', 'world', 'write', 'year', 'yellow', 'yesterday', 'zebra', 'zero', 'zest' ];
	}

	/**
	 * UK place names
	 *
	 * @return array
	 */
	protected function _getUkLocations()
	{
		return [ 'Bath', 'Birmingham', 'Bradford', 'Brighton', 'Hove', 'Bristol', 'Cambridge', 'Canterbury', 'Carlisle', 'Chester', 'Chichester', 'Coventry', 'Derby', 'Durham', 'Ely', 'Exeter', 'Gloucester', 'Hereford', 'Kingston', 'Hull', 'Lancaster', 'Leeds', 'Leicester', 'Lichfield', 'Lincoln', 'Liverpool', 'London', 'Manchester', 'Newcastle', 'Norwich', 'Nottingham', 'Oxford', 'Peterborough', 'Plymouth', 'Portsmouth', 'Preston', 'Ripon', 'Salford', 'Salisbury', 'Sheffield', 'Southampton', 'Stoke', 'Sunderland', 'Truro', 'Wakefield', 'Wells', 'Westminster', 'Winchester', 'Wolverhampton', 'Worcester', 'York', 'Aberdeen', 'Dundee', 'Edinburgh', 'Glasgow', 'Inverness', 'Stirling', 'Bangor', 'Cardiff', 'Newport', 'Swansea', 'Armagh', 'Belfast', 'Londonderry', 'Lisburn', 'Newry', 'Bedfordshire', 'Buckinghamshire', 'Cambridgeshire', 'Cheshire', 'Cornwall', 'Scilly', 'Cumbria', 'Derbyshire', 'Devon', 'Dorset', 'Durham', 'Sussex', 'Essex', 'Gloucestershire', 'Hampshire', 'Hertfordshire', 'Kent', 'Lancashire', 'Leicestershire', 'Lincolnshire', 'Merseyside', 'Norfolk', 'Northamptonshire', 'Northumberland', 'Nottinghamshire', 'Oxfordshire', 'Shropshire', 'Somerset', 'Staffordshire', 'Suffolk', 'Surrey', 'Warwickshire', 'Midlands', 'Sussex', 'Yorkshire', 'Wiltshire', 'Worcestershire', 'Flintshire', 'Glamorgan', 'Merionethshire', 'Monmouthshire', 'Montgomeryshire', 'Pembrokeshire', 'Radnorshire', 'Anglesey', 'Breconshire', 'Caernarvonshire', 'Cardiganshire', 'Carmarthenshire', 'Denbighshire', 'Kirkcudbrightshire', 'Lanarkshire', 'Midlothian', 'Moray', 'Nairnshire', 'Orkney', 'Peebleshire', 'Perthshire', 'Renfrewshire', 'Ross', 'Cromarty', 'Roxburghshire', 'Selkirkshire', 'Shetland', 'Stirlingshire', 'Sutherland', 'Wigtownshire', 'Aberdeenshire', 'Angus', 'Argyll', 'Ayrshire', 'Banffshire', 'Berwickshire', 'Bute', 'Caithness', 'Clackmannanshire', 'Dumfriesshire', 'Dumbartonshire', 'Lothian', 'Fife', 'Inverness', 'Kincardineshire', 'Kinross', 'Avon', 'Calder', 'Clyde', 'Dee', 'Derwent', 'Deveron', 'Findhorn', 'Forth', 'Humber', 'Lune', 'Medway', 'Mersey', 'Moray', 'Ness', 'Ouse', 'Ribble', 'Severn', 'Solway', 'Stour', 'Tay', 'Tees', 'Thames', 'Trent', 'Tweed', 'Tyne', 'Ythan', 'Wye', 'Carrauntoohil', 'Helvellyn', 'Lugnaquilla', 'Nevis', 'Scafell', 'Skiddaw', 'Snowdon' ];
	}

	/**
	 *World-wide place names
	 *
	 * @return array
	 */
	protected function _getWorldwideLocations()
	{
		return [ 'Abkhazia', 'Aden', 'Adriatic', 'Aegean', 'Afghanistan', 'Aland', 'Albania', 'Algeria', 'Amazon', 'Americas', 'Annapurna', 'Andaman', 'Andorra', 'Angola', 'Anguilla', 'Antarctic', 'Antigua', 'Antilles', 'Arctic', 'Argentina', 'Armenia', 'Aruba', 'Ascension', 'Ashmore', 'Asia', 'Atlantic', 'Australia', 'Australasia', 'Austria', 'Azerbaijan', 'Baffin', 'Bahamas', 'Bahrain', 'Baker', 'Baltic', 'Bangladesh', 'Barbados', 'Barbuda', 'Barents', 'Barthelemy', 'Belarus', 'Belgium', 'Belize', 'Bengal', 'Benin', 'Bering', 'Bermuda', 'Bhutan', 'Biscay', 'Bolivia', 'Bosnia', 'Bothnia', 'Botswana', 'Bouvet', 'Brazil', 'Brunei', 'Bulgaria', 'Burma', 'Burundi', 'Caicos', 'Caledonia', 'California', 'Cambodia', 'Cameroon', 'Canada', 'Carribean', 'Cartier', 'Caspian', 'Cayman', 'Chad', 'Chile', 'China', 'Clipperton', 'Cocos', 'Colombia', 'Colorado', 'Comoros', 'Congo', 'Cook', 'Croatia', 'Cuba', 'Cyprus', 'Cyprus', 'Denmark', 'Djibouti', 'Dneiper', 'Dominica', 'Ecuador', 'Egypt', 'Eritrea', 'Estonia', 'Ethiopia', 'Euphrates', 'Europe', 'Everest', 'Falklands', 'Faroes', 'Fiji', 'Finland', 'France', 'Futuna', 'Gabon', 'Gambia', 'Ganges', 'Georgia', 'Germany', 'Ghana', 'Gibraltar', 'Gobi', 'Greece', 'Greenland', 'Grenada', 'Grenadines', 'Guadeloupe', 'Guam', 'Guatemala', 'Guernsey', 'Guinea', 'Guyana', 'Haiti', 'Heard', 'Helena', 'Herzegovina', 'Honduras', 'Howland', 'Hungary', 'Iceland', 'India', 'Indonesia', 'Ionian', 'Iran', 'Iraq', 'Ireland', 'Israel', 'Italy', 'Jamaica', 'Japan', 'Jarvis', 'Jersey', 'Johnston', 'Jordan', 'Jupiter', 'Kalahari', 'Kangchenjunga', 'Kazakhstan', 'Keeling', 'Kenya', 'Kilimanjaro', 'Kingman', 'Kiribati', 'Kitts', 'Korea', 'Kuwait', 'Kyrgyzstan', 'Labrador', 'Laos', 'Latvia', 'Lebanon', 'Lena', 'Lesotho', 'Levantine', 'Liberia', 'Libya', 'Liechtenstein', 'Limpopo', 'Lithuania', 'Lucia', 'Luxembourg', 'Macau', 'Macedonia', 'Madagascar', 'Malacca', 'Malawi', 'Malaysia', 'Maldives', 'Mali', 'Malta', 'Mariana', 'Marshall', 'Martin', 'Martinique', 'Matterhorn', 'Mauritania', 'Mauritius', 'Mayotte', 'Mekong', 'Mexico', 'Micronesia', 'Midway', 'Miquelon', 'Moldova', 'Monaco', 'Mongolia', 'Montenegro', 'Montserrat', 'Morocco', 'Mozambique', 'Namibia', 'Nauru', 'Navassa', 'Nepal', 'Netherlands', 'Nevis', 'Nicaragua', 'Niger', 'Nigeria', 'Nile', 'Niue', 'Norway', 'Oman', 'Orinoco', 'Pacific', 'Pakistan', 'Palau', 'Palmyra', 'Panama', 'Paraguay', 'Patagonia', 'Peru', 'Philippines', 'Pierre', 'Pitcairn', 'Pluto', 'Poland', 'Polynesia', 'Portugal', 'Principe', 'Qatar', 'Reunion', 'Romania', 'Ross', 'Russia', 'Rwanda', 'Sahara', 'Samoa', 'Samoa', 'Sandwich', 'Saturn', 'Senegal', 'Serbia', 'Seychelles', 'Singapore', 'Slovakia', 'Slovenia', 'Solomon', 'Somalia', 'Somaliland', 'Spain', 'Sudan', 'Suez', 'Suriname', 'Svalbard', 'Swaziland', 'Sweden', 'Switzerland', 'Syria', 'Taiwan', 'Tajikistan', 'Tanzania', 'Tattoine', 'Thailand', 'Tigris', 'Tobago', 'Togo', 'Tokelau', 'Tonga', 'Transnistria', 'Trinidad', 'Tunisia', 'Turkey', 'Turkmenistan', 'Turks', 'Tuvalu', 'Uganda', 'Ukraine', 'Uruguay', 'Uzbekistan', 'Vanuatu', 'Vatican', 'Venezuela', 'Vietnam', 'Vincent', 'Wake', 'Wallis', 'Yangtzee', 'Yemen', 'Zambeze', 'Zambia', 'Zimbabwe' ];
	}
}
