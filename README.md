# plab-paytmrest

### Request    
`GET /V1/plab/paytm/params/:orderId`

### Response like    
```
[
  {
    "data": {
      "MID":"xHZThN98263666060403",
      "TXN_AMOUNT":34,
      "CHANNEL_ID":"WAP",
      "INDUSTRY_TYPE_ID":"Retail",
      "WEBSITE":"Website",
      "CUST_ID":"test@gmail.com",
      "ORDER_ID":"000000005",
      "EMAIL":"test@gmail.com",
      "CALLBACK_URL":"https://securegw.paytm.in/theia/paytmCallback?ORDER_ID=000000005"
      "CHECKSUMHASH":"k/rGlJ1eMBcKHnPKlPhDaX9D5afYDYv1JMONh2UhkT2l64drRi5I52jTm8aMhyfdUWeAgKrjUPmHEGhaQyAkk8rwu/u7ETMmW+Ff3PmorKs="
    },
    "status":"ok"
  }
]
```
