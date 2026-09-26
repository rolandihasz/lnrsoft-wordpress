var botName = "LnRSoft Bot",
botAvatar = "https://lnrsoft.com/wp-content/uploads/2019/05/bot.jpg.png",
conversationData = {"homepage": {1: { "statement": [ 
"Hello Friend! My name is LnRSoft Bot, I\'m the owner of this website and I\'d like to be your assistant here.", 
"Just a quick question.", 
"What is your name?", 
], "input": {"name": "name", "consequence": 1.2}},1.2:{"statement": function(context) {return [ 
"Nice to meet you here, " + context.name  + "!", 
"How you can see, our website is down for maintenance.", 
"We carry out some important changes and performance improvements on our site. We aim to come back online in the next 72 hours.", 
"Would you like contact us meantime?", 
];},"options": [{ "choice": "Yes","consequence": 1.4},{ 
"choice": "No","consequence": 1.5}]},1.4: { "statement": [ 
"Thank You! Please leave your email here and I will send you a message when it\'s ready.", 
], "email": {"email": "email", "consequence": 1.6}},1.5: {"statement": function(context) {return [ 
"Thank you for you visit, " + context.name  + ", See you next time…", 
];}},1.6: { "statement": [ 
"Got it! Thank you and see you soon here!", 
"Have a great day!", 
]}}};