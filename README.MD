# Cmtrading Autologin Plugin

1. #### The user enters the site using valid links:
- /autologin?...params
- /autologin_new?...params

2. #### GET Params:
- emailaddress
- account_no (Panda DB)
- action (ex. forexDeposit)
- lang (for redirect to specific site version)

3. #### The plugin checks the user's ip, if the ip is not allowed, we show an error
4. #### Check for a user in PANDA DB
   If the user finds it, then based on the lang parameter of its correspondence with the domain in the settings, it sends the user to the desired language version of the site with a token for autologin in panda (webtrader)