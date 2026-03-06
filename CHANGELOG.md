# DISCLAIMER ABOUT USING AI

As requested for transparency, I used the AI language model in brainstorming mode to discuss and validate my architectural ideas. All code, tests, and final decisions were written and made by me.

# CHANGELOG


## ver 0.1 - extract events to OOP for easier maintenance and expansion 
- add Event/EventInterface - basic contract
- add Event/AbstractEvent - for basic validation of common properties
- add Event/FoulEvent - full implementation of foul event
- add Event/GoalEvent - full implementation of goal event
- add EventType - an enum defining map between string type and class name for creation
- add EventFactory - event factory for creating instance of specific event
- modify EventHandler - implement changes to handler
- modify tests/Unit/EventHandlerTest - add tests for foul Event and invalid Event, modify exception tests for matching exception and message, modify testEventIsSavedToFile to contain the correct eventData
- modify tests/Api/EventApiCest - add tests for goal Event and invalid Event, modify exception message


## TODO
- refactor data fields validation - it is not unified between events classes and abstract, tests not cover specific fields validation for type event - only common fields validated in abstract class.