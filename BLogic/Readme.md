#BLogic - The Business Logic Web Framework

The BLogic framework is an object orientated Model/Object/View PHP code library and engine designed to make the construction of complex fully custom web apps more efficient and maintainable over time. It adopts best practice methods of separating out various functions required by a database driven web system while avoiding making everything more complex than necessary by keeping the layers of abstraction to a minimum.


### Main Features
- Database syntax is handled by ‘DataSource’ and 'Qualifier' objects that keeps it separate from the web app logic. 
- Database tables are represented by ‘Entities’, which interact with the data source.
- Pages and forms are called ‘Components’ that come in a pair of files:
    - The HTML template file which contains, when possible, only layout logic. 
    - The controller class which handles all of the main logic for the page such as database operations, saving, cancelling, validation etc.
- A centralised point of operation that all requests filter through called the ‘RequestResponseHandler’
- Various utility methods that cover a wide aspect of operations that a developer might need when building an app, such as form construction, data validation and data manipulation.
- Support for dynamic links (known as 'Perma-links' or 'routing').
- Basic support for REST protocol.

### Documentation
The main documentation is forthcoming. 
