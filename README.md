DigiComp.Sequence
-------------------------


This is a very simple and stupid tool, helping in generation of gapless sequences. For this task it relies on key 
integrity of the database of your choice.

Usage is quite simple also:

	/**
	 * @param \DigiComp\Sequence\Service\SequenceNumberGenerator $sequenceNumberGenerator
	 */
	public function __construct(SequenceNumberGenerator $sequenceNumberGenerator) 
	{
		$this->orderId = $sequenceNumberGenerator->getNextNumberFor($this);		
	}

``getNextNumberFor`` allows you to give an object which will be resolved to its FQCN or a custom sequence name.

The CommandController helps you to advance the current sequence number, in case of migrations or similar.

See ``./flow help sequence:advance`` if interested.
