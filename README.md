ProophEventSourcing
===================

Simple and lightweight event sourcing library with out of the box support for [ProophEventStore](https://github.com/prooph/event-store)

[![Build Status](https://travis-ci.org/prooph/event-sourcing.svg?branch=master)](https://travis-ci.org/prooph/event-sourcing)
[![Coverage Status](https://img.shields.io/coveralls/prooph/event-sourcing.svg)](https://coveralls.io/r/prooph/event-sourcing?branch=master)
[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/prooph/improoph)

# Installation

You can install ProophEventSourcing via composer by adding `"prooph/event-sourcing": "^5.0"` as requirement to your composer.json.

# Usage 

Our [quickstart](https://github.com/prooph/event-sourcing/blob/master/examples/quickstart.php) should give you a starting point.
It's a very small domain but shows you the useage of ProophEventSourcing and the integration with ProophEventStore.


# ProophEventStore Integration

ProophEventSourcing ships with a [ProophEventStore](https://github.com/prooph/event-store) AggregateTranslator to connect the store
with the bundled [AggregateRoot](https://github.com/prooph/event-sourcing/blob/master/src/Prooph/EventSourcing/AggregateRoot.php).

# Support

- Ask questions on Stack Overflow tagged with [#prooph](https://stackoverflow.com/questions/tagged/prooph).
- File issues at [https://github.com/prooph/event-sourcing/issues](https://github.com/prooph/event-sourcing/issues).
- Say hello in the [prooph gitter](https://gitter.im/prooph/improoph) chat.

# Used Third-Party Libraries

- Uuids of the AggregateChangedEvents are generated with [ramsey/uuid](https://github.com/ramsey/uuid)
- Assertions are performed by [beberlei/assert](https://github.com/beberlei/assert)




