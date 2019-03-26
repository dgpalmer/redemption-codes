# Redemption Codes

The Redemptions Code module provides a toolset for managing and granting digital entitlements in a Drupal 8 application.

### Redemption Codes

  - View Redemption Codes 
  - Create Redemption Codes & assign them redemption actions through the Drupal admin interfqace
  - Import Redemption Codes from CSV and assign them Redemption Actions
  - Redeem a Redemption Code to receive a digital entitlement through a Redemption Action

### Redemption Actions

  - Can be any Drupal "Action"
  - Grant a Role, Remove a Role, Block the User, etc.

### Settings
  - Navigate to admin/structure/redemption_codes/settings
  - You can change the default path for the redemption as well as what path the user is returned to upon successful redemption.
  - You can also configure some different flows, such as registration flow (puts a redemption code field on the user registration form so new users can immediately redeem a code) or a pre-registration flow, users redeem a code, then register an account.
  Note: These require the Appreciation Engine Module
  - You can also disable the enforcement of Unique Codes, allowing the same string to be used for multiple codes, this is disabled by default.

### Try It Out

- Install the redemption_codes module and its required dependencies (migrate_tools, migrate_plus, migrate_source_csv)
- Create a Code through the UI at admin/structure/redemption_code/add
    - Be sure to Add a Redemption Action, such as Add the Administrator role to the selected user(s)
- Import the sample csv (artifacts/example_codes.csv) at admin/structure/redemption_codes/csv
    - Be sure to Add a default Redemption action, such as Add the Administrator role to the selected user(s)
- Once you have some codes you created in the UI or imported from CSV, you can try them out.
    - Go to /redeem and try the following
        - Put in an incorrect code, it should tell you the code is invalid
        - Put in a correct code, it will perform the redemption actions (grant the Admin Role) and associate the redemption code with the User that you selected.
        - Put in an already used code, it should tell you the code has already been claimed.

### Questions
Email Donovan Palmer (donovan@donovanpalmer.net)

