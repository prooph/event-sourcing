ProophEventSourcing
===================

Simple and lightweight event sourcing library with out of the box support for [ProophEventStore](https://github.com/prooph/event-store)

[![Build Status](https://travis-ci.org/prooph/event-sourcing.svg?branch=master)](https://travis-ci.org/prooph/event-sourcing)
[![Coverage Status](https://img.shields.io/coveralls/prooph/event-sourcing.svg)](https://coveralls.io/r/prooph/event-sourcing?branch=master)

# The Heart Of Prooph Link

Prooph software GmbH is maintaining the open source software [prooph link](https://github.com/prooph/link),
a data linking and workflow processing application based on PHP 5.5+ and some great libraries from the PHP universe.
Four of these libraries are developed and maintained directly by us. ProophEventSourcing is one of them. The others are
[ProophEventStore](https://github.com/prooph/event-store), [ProophServiceBus](https://github.com/prooph/service-bus) and [prooph processing](https://github.com/prooph/processing).

#Installation

You can install ProophEventSourcing via composer by adding `"prooph/event-sourcing": "~1.0"` as requirement to your composer.json.

#Usage 

Our [quickstart](https://github.com/prooph/event-sourcing/blob/master/examples/quickstart.php) should give you a starting point.
It's a very small domain but shows you the useage of ProophEventSourcing and the integration with ProophEventStore.

#ProophEventStore Support

ProophEventSourcing ships with a [ProophEventStore](https://github.com/prooph/event-store) AggregateTranslator to connect the store
with the bundled [AggregateRoot](https://github.com/prooph/event-sourcing/blob/master/src/Prooph/EventSourcing/AggregateRoot.php).

# Support

- Ask any questions on [prooph-users](https://groups.google.com/forum/?hl=de#!forum/prooph) google group.
- File issues at [https://github.com/prooph/event-sourcing/issues](https://github.com/prooph/event-sourcing/issues).

#Used Third-Party Libraries

- Uuids of the AggregateChangedEvents are generated with [rhumsaa/uuid](https://github.com/ramsey/uuid)
- Assertions are performed by [beberlei/assert](https://github.com/beberlei/assert)
- ArrayReader to access payload properties of Events with ease is powered by [codeliner/array-reader](https://github.com/codeliner/array-reader)




