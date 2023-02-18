# DigiComp.Sequence

![Build status](https://ci.digital-competence.de/api/badges/Packages/DigiComp.FlowObjectResolving/status.svg)

This is a very simple tool, helping in generation of gapless sequences. For this task it relies on key integrity of the
database of your choice.

Usage is quite simple also:

```php
    /**
     * @param SequenceGenerator $sequenceGenerator
     */
    public function __construct(SequenceGenerator $sequenceNumberGenerator)
    {
        $this->orderId = $sequenceGenerator->getNextNumberFor($this);
    }
```

`getNextNumberFor` allows you to give an object (which will be resolved to its FQCN) or a custom sequence name.

The `SequenceCommandController` helps you to set the last sequence number, in case of migrations or similar. See
`./flow help sequence:setlastnumberfor` if interested.
