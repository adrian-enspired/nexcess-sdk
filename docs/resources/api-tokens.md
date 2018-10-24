-----
Title: Api Tokens
SDK Module: ApiToken
-----
<div align="center">
  <img src="https://raw.githubusercontent.com/nexcess/nexcess-php-sdk/master/.github/nexcess.png" alt="nexcess.net"/><br/>
  <img src="https://raw.githubusercontent.com/nexcess/nexcess-php-sdk/master/.github/thermo.png" alt="thermo.io"/><br/>
</div>

# Api Tokens

Authorization tokens for using the Nexcess/Thermo API and client web portals.

**Module Name**: `ApiToken`

**Endpoint**: `Nexcess\Sdk\Resource\ApiToken\Endpoint`

**Entity**: `Nexcess\Sdk\Resource\ApiToken\Entity`

## Entity Properties

### Writable Properties
- **`name`** (string): A user-assigned identifier for the Token.

### Readonly Properties
- **`identity`** (string): System-generated description.
- **`token`** (string): The Token itself. Note this property will only have a value when the token is first created, and will be `null` afterwards. Be sure to write it down and store it in a safe place.

## Endpoint Actions

- [Create a New Api Token](api-token/create-a-new-api-token.md)
- [Update an Api Token](api-token/update-an-api-token.md)
- [Delete an Api Token](api-token/delete-an-api-token.md)
