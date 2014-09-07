ProophEventSourcing
===================

Simple and lightweight event sourcing library with out of the box support for [ProophEventStore](https://github.com/prooph/event-store)

[![Build Status](https://travis-ci.org/prooph/event-sourcing.svg?branch=master)](https://travis-ci.org/prooph/event-sourcing)
[![Coverage Status](https://coveralls.io/repos/prooph/event-sourcing/badge.png)](https://coveralls.io/r/prooph/event-sourcing)

#Used Third-Party Libraries

- Uuids of the AggregateChangedEvents are generated with [rhumsaa/uuid](https://github.com/ramsey/uuid)
- Assertions are performed by [beberlei/assert](https://github.com/beberlei/assert)
- ArrayReader to access payload properties of Events with ease is powered by [codeliner/array-reader](https://github.com/codeliner/array-reader)

#ProophEventStore Support

ProophEventSourcing ships with a [ProophEventStore](https://github.com/prooph/event-store) AggregateTranslator to connect the store
with the bundled [AggregateRoot](https://github.com/prooph/event-sourcing/blob/master/src/Prooph/EventSourcing/AggregateRoot.php).

#Example Usage

Our [quickstart](https://github.com/prooph/event-sourcing/blob/master/src/Prooph/EventSourcing/examples/quickstart.php) should give you a starting point.
It's a very small domain but shows you the useage of ProophEventSourcing and the integration with ProophEventStore.


