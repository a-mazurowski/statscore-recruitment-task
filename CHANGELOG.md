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

## ver 0.2 - modify storage from files to sqlite
- add Storage/StorageInterface - basic contract
- add Storage/SqliteStorage - Sqlite instance for StorageInterface
- add database.sql - initial migration for database
- add .env.dist - .env distributable file
- modify index.php - implements changes with Storage, check env for storage type
- modify FileStorage - implements StorageInterface and save statistics
- modify EventHandler - implements changes with Storage
- modify docker-compose.yml - add env-file
- fix AbstractEvent - missing 'data' key in toArray
- fix .gitignore

## TODO
- refactor data fields validation - it is not unified between events classes and abstract, tests not cover specific fields validation for type event - only common fields validated in abstract class.
- refactor class structure - atm class separation is a little bit messy
- im not sure about testing different storage implementation
- better migrations or loading database on start - inserting tables on non exist database.sqlite is bad