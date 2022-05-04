# DigiComp.Sequence

This is a very simple tool, helping in generation of gapless sequences. For this task it relies on key integrity of the
database of your choice.

Usage is quite simple also:

```php
/**
 * @param SequenceNumberGenerator $sequenceNumberGenerator
 */
public function __construct(SequenceNumberGenerator $sequenceNumberGenerator)
{
    $this->orderId = $sequenceNumberGenerator->getNextNumberFor($this);
}
```

`getNextNumberFor` allows you to give an object (which will be resolved to its FQCN) or a custom sequence name.

The `SequenceCommandController` helps you to set the last sequence number, in case of migrations or similar. See
`./flow help sequence:setlastnumberfor` if interested.
