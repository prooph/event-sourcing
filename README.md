ProophEventSourcing
===================

Provides basic functionality for event-sourced aggregates

[![Build Status](https://travis-ci.org/prooph/event-sourcing.svg?branch=master)](https://travis-ci.org/prooph/event-sourcing)

#Used Third-Party Libraries

- ProophEventSourcing uses [ZF2 components](http://framework.zend.com/) to offer event-driven capabilities.
- Uuids of the AggregateChangedEvents are generated with [rhumsaa/uuid](https://github.com/ramsey/uuid)
- Assertions are performed by [beberlei/assert](https://github.com/beberlei/assert)
- Immutable DateTime ValueObjects are provided by [nicolopignatelli/valueobjects](https://github.com/nicolopignatelli/valueobjects)
- ArrayReader to access payload properties of Events with ease is powered by [codeliner/array-reader](https://github.com/codeliner/array-reader)

#ProophEventStore Support

ProophEventSourcing ships with a [ProophEventStore](https://github.com/prooph/event-store) Feature to connect the store
with the bundled [EventSourcingRepository](https://github.com/prooph/event-sourcing/blob/master/src/Prooph/EventSourcing/Repository/EventSourcingRepository.php).

#Quick Start

If you want to see ProophEventSourcing in action you can have a look at the [Quick Start of ProophEventStore](https://github.com/prooph/event-store#quick-start)
