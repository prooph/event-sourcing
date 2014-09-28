ProophEventSourcing
===================

Simple and lightweight event sourcing library with out of the box support for [ProophEventStore](https://github.com/prooph/event-store)

[![Build Status](https://travis-ci.org/prooph/event-sourcing.svg?branch=master)](https://travis-ci.org/prooph/event-sourcing)
[![Coverage Status](https://img.shields.io/coveralls/prooph/event-sourcing.svg)](https://coveralls.io/r/prooph/event-sourcing?branch=master)

#About Prooph

Prooph is the organisation behind [gingerframework](https://github.com/gingerframework/gingerframework) - a workflow framework written in PHP.
The founder and lead developer is [codeliner](https://github.com/codeliner). Prooph provides CQRS+ES infrastructure components for the gingerframework.
The components are split into 3 major libraries [ProophServiceBus](https://github.com/prooph/service-bus), [ProophEventSourcing](https://github.com/prooph/event-sourcing),
[ProophEventStore](https://github.com/prooph/event-store) and various minor libraries which add additional features or provide support for other frameworks.
The public APIs of the major components are stable. They are loosely coupled among each other and with the gingerframework, so you can mix and match them with
other libraries.

#Installation

You can install ProophEventSourcing via composer by adding `"prooph/event-sourcing": "~1.0"` as requirement to your composer.json.

#Usage 

Our [quickstart](https://github.com/prooph/event-sourcing/blob/master/examples/quickstart.php) should give you a starting point.
It's a very small domain but shows you the useage of ProophEventSourcing and the integration with ProophEventStore.

#ProophEventStore Support

ProophEventSourcing ships with a [ProophEventStore](https://github.com/prooph/event-store) AggregateTranslator to connect the store
with the bundled [AggregateRoot](https://github.com/prooph/event-sourcing/blob/master/src/Prooph/EventSourcing/AggregateRoot.php).

#Used Third-Party Libraries

- Uuids of the AggregateChangedEvents are generated with [rhumsaa/uuid](https://github.com/ramsey/uuid)
- Assertions are performed by [beberlei/assert](https://github.com/beberlei/assert)
- ArrayReader to access payload properties of Events with ease is powered by [codeliner/array-reader](https://github.com/codeliner/array-reader)




