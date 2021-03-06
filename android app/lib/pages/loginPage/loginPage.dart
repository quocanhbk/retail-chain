import 'dart:io';

import 'package:bkrm/services/api.dart';
import 'package:bkrm/services/info/managementInfo/userInfo.dart';
import 'package:bkrm/services/services.dart';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter/widgets.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:bkrm/pages/Nav2App.dart';
import 'package:local_auth/auth_strings.dart';
import 'package:local_auth/local_auth.dart';
import 'Widget/bezierContainer.dart';
import 'package:local_auth/error_codes.dart' as auth_error;
import 'package:system_settings/system_settings.dart';

class LoginPage extends StatefulWidget {
  LoginPage(
      {Key? key, this.title, this.userName, this.password, this.lastLoggedIn})
      : super(key: key);
  final String? userName;
  final String? password;
  final String? title;
  final UserInfo? lastLoggedIn;
  @override
  _LoginPageState createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  TextEditingController usernameController = TextEditingController();
  TextEditingController passwordController = TextEditingController();
  MsgInfoCode? msgReturn;
  bool connIssue = false;
  bool usernameValid = true;
  bool passwordValid = true;
  bool stayLoggedIn = false;
  final _formKey = GlobalKey<FormState>();

  FocusNode? node;

  @override
  void initState() {
    if (widget.userName != null) {
      usernameController.text = widget.userName!;
      usernameValid = true;
    }
    if (widget.password != null) {
      passwordController.text = widget.password!;
      passwordValid = true;
    }
    if (widget.lastLoggedIn != null) {
      lastLogedInUser = widget.lastLoggedIn;
      debugPrint(lastLogedInUser.toString());
      if (widget.lastLoggedIn!.stayLoggedIn == true) {
        Future.delayed(Duration(milliseconds: 100), () {
          automaticallyLogInForUser();
        });
      }
    }
    super.initState();
  }

  UserInfo? lastLogedInUser;

  automaticallyLogInForUser() async {
    await BkrmService().networkAvailableCheck();
    if (!BkrmService().networkAvailable) {
      bool localAuth = await authenticateOnLocal();
      if (!localAuth) {
        return;
      } else {
        showDialog(
            context: context,
            builder: (context) {
              return Material(
                type: MaterialType.transparency,
                child: Container(
                  height: 250,
                  child: Center(
                    child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Center(child: CircularProgressIndicator()),
                          Container(
                            height: 30,
                          ),
                          Text(
                            "??ang x??? l??. Xin vui l??ng ?????i !",
                            style: TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 16,
                                color: Colors.grey),
                          )
                        ]),
                  ),
                ),
              );
            });
        await BkrmService()
            .logInUser(usernameController.text, passwordController.text,
                userRefresh: lastLogedInUser)
            .then((result) {
          print("Result: " + result.toString());
          if (result == MsgInfoCode.logInSucess) {
            bool stayLoggedInUser = false;
            if (widget.lastLoggedIn != null) {
              stayLoggedInUser = widget.lastLoggedIn!.stayLoggedIn;
            }
            if (stayLoggedIn || stayLoggedInUser) {
              BkrmService().currentUser!.stayLoggedIn = true;
            }
            Navigator.pushNamedAndRemoveUntil(
                context, Nav2App.selectModuleroute, (route) => false);
          } else {
            setState(() {
              msgReturn = result;
              if (msgReturn == MsgInfoCode.serverNotAvailable) {
                connIssue = true;
                return;
              } else {
                connIssue = false;
                return;
              }
            });
            Navigator.pop(context);
          }
        });
      }
      return;
    }
    if (widget.lastLoggedIn != null) {
      if (widget.lastLoggedIn!.stayLoggedIn == true) {
        showDialog(
            context: context,
            builder: (context) {
              return Material(
                type: MaterialType.transparency,
                child: Container(
                  height: 250,
                  child: Center(
                    child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Center(child: CircularProgressIndicator()),
                          Container(
                            height: 30,
                          ),
                          Text(
                            "??ang ????ng nh???p. Xin vui l??ng ?????i!",
                            style: TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 16,
                                color: Colors.grey),
                          )
                        ]),
                  ),
                ),
              );
            });
        String? token = await refreshToken(widget.lastLoggedIn!);
        Navigator.pop(context);
        if (token == null) {
          setState(() {
            stayLoggedIn = false;
          });
          showDialog(
              context: context,
              builder: (context) {
                return AlertDialog(
                  title: Text("Th??ng b??o"),
                  content: Text(
                      "Phi??n ????ng nh???p ???? h???t h???n. Vui l??ng ????ng nh???p l???i!"),
                  actions: [
                    TextButton(
                        onPressed: () {
                          Navigator.pop(context);
                        },
                        child: Text("????ng"))
                  ],
                );
              });
          widget.lastLoggedIn!.stayLoggedIn = false;
          await BkrmService().storeUserLoggedIn(widget.lastLoggedIn!);
        } else {
          lastLogedInUser!.token = token;
          BkrmService bkrmServices = BkrmService();
          showDialog(
              context: context,
              builder: (context) {
                return Material(
                  type: MaterialType.transparency,
                  child: Container(
                    height: 250,
                    child: Center(
                      child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Center(child: CircularProgressIndicator()),
                            Container(
                              height: 30,
                            ),
                            Text(
                              "??ang x??? l??. Xin vui l??ng ?????i !",
                              style: TextStyle(
                                  fontWeight: FontWeight.bold,
                                  fontSize: 16,
                                  color: Colors.grey),
                            )
                          ]),
                    ),
                  ),
                );
              });
          await bkrmServices
              .logInUser(usernameController.text, passwordController.text,
                  userRefresh: lastLogedInUser)
              .then((result) {
            print("Result: " + result.toString());
            if (result == MsgInfoCode.logInSucess) {
              bool stayLoggedInUser = false;
              if (widget.lastLoggedIn != null) {
                stayLoggedInUser = widget.lastLoggedIn!.stayLoggedIn;
              }
              if (stayLoggedIn || stayLoggedInUser) {
                BkrmService().currentUser!.stayLoggedIn = true;
              }
              Navigator.pushNamedAndRemoveUntil(
                  context, Nav2App.selectModuleroute, (route) => false);
            } else {
              setState(() {
                msgReturn = result;
                if (msgReturn == MsgInfoCode.serverNotAvailable) {
                  connIssue = true;
                  return;
                } else {
                  connIssue = false;
                  return;
                }
              });
              Navigator.pop(context);
            }
          });
        }
      }
    }
  }

  Future<String?> refreshToken(UserInfo userInfo) async {
    Map<String, dynamic> refreshResponse =
        await ApiService().refreshToken(token: userInfo.token);
    if (refreshResponse["state"] == "success") {
      return refreshResponse["token"];
    } else {
      return null;
    }
  }

  Widget _backButton() {
    return InkWell(
      onTap: () {
        Navigator.pop(context);
      },
      child: Container(
        padding: EdgeInsets.symmetric(horizontal: 10),
        child: Row(
          children: <Widget>[
            Container(
              padding: EdgeInsets.only(left: 0, top: 10, bottom: 10),
              child: Icon(Icons.keyboard_arrow_left, color: Colors.black),
            ),
            Text('????ng',
                style: TextStyle(fontSize: 12, fontWeight: FontWeight.w500))
          ],
        ),
      ),
    );
  }

  Widget _entryField(String title, TextEditingController controller,
      {bool isPassword = false, String? Function(String?)? validator}) {
    return Container(
      margin: EdgeInsets.symmetric(vertical: 10),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          Text(
            title,
            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
          ),
          SizedBox(
            height: 10,
          ),
          TextFormField(
              validator: validator == null ? null : validator,
              controller: controller,
              obscureText: isPassword,
              textInputAction:
                  isPassword ? TextInputAction.send : TextInputAction.next,
              onEditingComplete: () async {
                if (isPassword) {
                  await BkrmService().networkAvailableCheck();
                  if (!BkrmService().networkAvailable) {
                    passwordValid = true;
                  }
                  if (passwordValid && usernameValid) {
                    if (!BkrmService().networkAvailable) {
                      bool localAuthenticate = await authenticateOnLocal();
                      if (!localAuthenticate) {
                        return;
                      }
                    }
                    BkrmService bkrmServices = BkrmService();
                    showDialog(
                        context: context,
                        builder: (context) {
                          return Material(
                            type: MaterialType.transparency,
                            child: Container(
                              height: 250,
                              child: Center(
                                child: Column(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Center(
                                          child: CircularProgressIndicator()),
                                      Container(
                                        height: 30,
                                      ),
                                      Text(
                                        "??ang x??? l??. Xin vui l??ng ?????i !",
                                        style: TextStyle(
                                            fontWeight: FontWeight.bold,
                                            fontSize: 16,
                                            color: Colors.grey),
                                      )
                                    ]),
                              ),
                            ),
                          );
                        });
                    MsgInfoCode returnCode = await bkrmServices.logInUser(
                        usernameController.text, passwordController.text);
                    debugPrint("Result: " + returnCode.toString());
                    if (returnCode == MsgInfoCode.logInSucess) {
                      bool stayLoggedInUser = false;
                      if (widget.lastLoggedIn != null) {
                        stayLoggedInUser = widget.lastLoggedIn!.stayLoggedIn;
                      }
                      if (stayLoggedIn || stayLoggedInUser) {
                        BkrmService().currentUser!.stayLoggedIn = true;
                        BkrmService()
                            .storeUserLoggedIn(BkrmService().currentUser!);
                      }
                      Navigator.pushNamedAndRemoveUntil(
                          context, Nav2App.selectModuleroute, (route) => false);
                    } else {
                      setState(() {
                        msgReturn = returnCode;
                        if (msgReturn == MsgInfoCode.serverNotAvailable) {
                          connIssue = true;
                          return;
                        } else {
                          connIssue = false;
                          return;
                        }
                      });
                      Navigator.pop(context);
                    }
                  }
                } else {
                  node!.nextFocus();
                }
              },
              decoration: InputDecoration(
                  border: UnderlineInputBorder(
                      borderSide: BorderSide(color: Colors.grey)),
                  fillColor: Color(0xfff3f3f4),
                  filled: true))
        ],
      ),
    );
  }

  Future<bool> authenticateOnLocal() async {
    try {
      const androidString = const AndroidAuthMessages(
          biometricHint: "",
          biometricRequiredTitle: "X??c minh danh t??nh",
          deviceCredentialsRequiredTitle: "X??c minh danh t??nh",
          deviceCredentialsSetupDescription: "X??c minh danh t??nh",
          signInTitle: "X??c minh danh t??nh",
          cancelButton: 'H???y',
          goToSettingsButton: 'C??i ?????t',
          goToSettingsDescription: 'Vui l??ng thi???t l???p ph????ng th???c b???o m???t');
      bool authenticated = await LocalAuthentication().authenticate(
          localizedReason: 'H??y x??c minh ????? ????ng nh???p v??o h??? th???ng',
          useErrorDialogs: false,
          androidAuthStrings: androidString);
      if (!authenticated) {
        showDialog(
            context: navigatorKey.currentContext!,
            builder: (context) {
              return AlertDialog(
                title: Text("X??c minh th???t b???i"),
                actions: [
                  TextButton(
                      onPressed: () {
                        Navigator.pop(context);
                      },
                      child: Text("????ng"))
                ],
              );
            });
        return false;
      } else {
        return true;
      }
    } on PlatformException catch (e) {
      if (e.code == auth_error.notAvailable) {
        showDialog(
            context: context,
            builder: (context) {
              return AlertDialog(
                title: Text("Ch??a c??i ?????t ph????ng th???c b???o m???t"),
                content: Text(
                    "C??i ?????t kh??a m??n h??nh cho ??i???n tho???i ????? c?? th??? truy c???p ch??? ????? offline."),
                actions: [
                  TextButton(
                    onPressed: () {
                      Navigator.pop(context);
                    },
                    child: Text("????ng"),
                  ),
                  TextButton(
                    onPressed: () {
                      SystemSettings.security();
                    },
                    child: Text("C??i ?????t"),
                  ),
                ],
              );
            });
      }
      return false;
    }
  }

  Widget _submitButton() {
    return InkWell(
      onTap: () async {
        await BkrmService().networkAvailableCheck();
        if (!BkrmService().networkAvailable) {
          passwordValid = true;
        }
        if (passwordValid && usernameValid) {
          if (!BkrmService().networkAvailable) {
            bool localAuthenticate = await authenticateOnLocal();
            if (!localAuthenticate) {
              return;
            }
          }
          BkrmService bkrmServices = BkrmService();
          showDialog(
              context: context,
              builder: (context) {
                return Material(
                  type: MaterialType.transparency,
                  child: Container(
                    height: 250,
                    child: Center(
                      child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Center(child: CircularProgressIndicator()),
                            Container(
                              height: 30,
                            ),
                            Text(
                              "??ang x??? l??. Xin vui l??ng ?????i !",
                              style: TextStyle(
                                  fontWeight: FontWeight.bold,
                                  fontSize: 16,
                                  color: Colors.grey),
                            )
                          ]),
                    ),
                  ),
                );
              });
          MsgInfoCode returnCode = await bkrmServices.logInUser(
              usernameController.text, passwordController.text);
          debugPrint("Result: " + returnCode.toString());
          if (returnCode == MsgInfoCode.logInSucess) {
            bool stayLoggedInUser = false;
            if (widget.lastLoggedIn != null) {
              stayLoggedInUser = widget.lastLoggedIn!.stayLoggedIn;
            }
            if (stayLoggedIn || stayLoggedInUser) {
              BkrmService().currentUser!.stayLoggedIn = true;
              BkrmService().storeUserLoggedIn(BkrmService().currentUser!);
            }
            Navigator.pushNamedAndRemoveUntil(
                context, Nav2App.selectModuleroute, (route) => false);
          } else {
            debugPrint("Log in not success");
            debugPrint("Is stay logged In ?");
            setState(() {
              msgReturn = returnCode;
              if (msgReturn == MsgInfoCode.serverNotAvailable) {
                connIssue = true;
                return;
              } else {
                connIssue = false;
                return;
              }
            });
            Navigator.pop(context);
          }
        }
      },
      child: Container(
        width: MediaQuery.of(context).size.width,
        padding: EdgeInsets.symmetric(vertical: 15),
        alignment: Alignment.center,
        decoration: BoxDecoration(
            borderRadius: BorderRadius.all(Radius.circular(5)),
            boxShadow: <BoxShadow>[
              BoxShadow(
                  color: Colors.grey.shade200,
                  offset: Offset(2, 4),
                  blurRadius: 5,
                  spreadRadius: 2)
            ],
            gradient: LinearGradient(
                begin: Alignment.centerLeft,
                end: Alignment.centerRight,
                colors: [Color(0xff1565c0), Color(0xff90caf9)])),
        child: Text(
          '????ng nh???p',
          style: TextStyle(fontSize: 20, color: Colors.white),
        ),
      ),
    );
  }

  Widget _divider() {
    return Container(
      margin: EdgeInsets.symmetric(vertical: 10),
      child: Row(
        children: <Widget>[
          SizedBox(
            width: 20,
          ),
          Expanded(
            child: Padding(
              padding: EdgeInsets.symmetric(horizontal: 10),
              child: Divider(
                thickness: 1,
              ),
            ),
          ),
/*          Text('ho???c '),
          Expanded(
            child: Padding(
              padding: EdgeInsets.symmetric(horizontal: 10),
              child: Divider(
                thickness: 1,
              ),
            ),
          ),*/
          SizedBox(
            width: 20,
          ),
        ],
      ),
    );
  }

  Widget _facebookButton() {
    return Container(
      height: 50,
      margin: EdgeInsets.symmetric(vertical: 20),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.all(Radius.circular(10)),
      ),
      child: Row(
        children: <Widget>[
          Expanded(
            flex: 1,
            child: Container(
              decoration: BoxDecoration(
                color: Color(0xff1959a9),
                borderRadius: BorderRadius.only(
                    bottomLeft: Radius.circular(5),
                    topLeft: Radius.circular(5)),
              ),
              alignment: Alignment.center,
              child: Text('f',
                  style: TextStyle(
                      color: Colors.white,
                      fontSize: 25,
                      fontWeight: FontWeight.w400)),
            ),
          ),
          Expanded(
            flex: 5,
            child: Container(
              decoration: BoxDecoration(
                color: Color(0xff2872ba),
                borderRadius: BorderRadius.only(
                    bottomRight: Radius.circular(5),
                    topRight: Radius.circular(5)),
              ),
              alignment: Alignment.center,
              child: Text('????ng nh???p v???i Facebook',
                  style: TextStyle(
                      color: Colors.white,
                      fontSize: 18,
                      fontWeight: FontWeight.w400)),
            ),
          ),
        ],
      ),
    );
  }

  Widget _createAccountLabel() {
    return InkWell(
      onTap: () {
        BkrmService().networkAvailableCheck();
        Navigator.pushNamed(context, Nav2App.signUpRoute);
      },
      child: Container(
        margin: EdgeInsets.symmetric(vertical: 20),
        padding: EdgeInsets.all(15),
        alignment: Alignment.bottomCenter,
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: <Widget>[
            Text(
              'Kh??ng c?? t??i kho???n ?',
              style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
            ),
            SizedBox(
              width: 10,
            ),
            Text(
              '????ng k??',
              style: TextStyle(
                  color: Color(0xfff79c4f),
                  fontSize: 13,
                  fontWeight: FontWeight.w600),
            ),
          ],
        ),
      ),
    );
  }

  Widget _title() {
    return RichText(
      textAlign: TextAlign.center,
      text: TextSpan(
          text: 'B',
          style: GoogleFonts.portLligatSans(
            textStyle: Theme.of(context).textTheme.headline4,
            fontSize: 40,
            fontWeight: FontWeight.w700,
            color: Color(0xff1565c0),
          ),
          children: [
            TextSpan(
              text: 'K',
              style: TextStyle(color: Color(0xff1565c0), fontSize: 40),
            ),
            TextSpan(
              text: 'RM',
              style: TextStyle(color: Colors.black, fontSize: 40),
            ),
          ]),
    );
  }

  Widget _usernamePasswordWidget() {
    if (lastLogedInUser != null) {
      usernameController.text = lastLogedInUser!.username;
    }
    return Form(
        autovalidateMode: AutovalidateMode.always,
        key: _formKey,
        child: Column(children: <Widget>[
          lastLogedInUser == null
              ? _entryField("T??n ????ng nh???p ", usernameController,
                  validator: (username) {
                  if (username == null || username == "") {
                    usernameValid = false;
                    return "* B??t bu???c";
                  }
                  usernameValid = true;
                  return null;
                })
              : Center(
                  child: Text(
                    lastLogedInUser!.username,
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
                  ),
                ),
          SizedBox(
            height: 10,
          ),
          lastLogedInUser == null
              ? Container()
              : InkWell(
                  onTap: () async {
                    await BkrmService().clearLastUserLoggedIn();
                    lastLogedInUser = null;
                    usernameController.clear();
                    setState(() {});
                  },
                  child: Center(
                    child: Text(
                      "Kh??ng ph???i b???n?",
                      style: TextStyle(
                          color: Color(0xfff79c4f),
                          fontSize: 13,
                          fontWeight: FontWeight.w600),
                    ),
                  ),
                ),
          lastLogedInUser == null
              ? Container()
              : SizedBox(
                  height: 30,
                ),
          _entryField("M???t kh???u ", passwordController, isPassword: true,
              validator: (password) {
            if (!BkrmService().networkAvailable) {
              passwordValid = true;
              return null;
            }
            if (password == null || password == "") {
              passwordValid = false;
              return " * B??t bu???c";
            } else {
              passwordValid = true;
              return null;
            }
          }),
        ]));
  }

  Widget _notice() {
    if (connIssue) {
      return Text(
        "L???i k???t n???i m???ng",
        style: TextStyle(
            color: Colors.red, fontWeight: FontWeight.bold, fontSize: 16),
      );
    }
    if (msgReturn == MsgInfoCode.wrongPasswordOrUsername) {
      return Text(
        "Sai t??n ????ng nh???p ho???c m???t kh???u",
        style: TextStyle(
            color: Colors.red, fontWeight: FontWeight.bold, fontSize: 16),
      );
    }
    return Container();
  }

  @override
  Widget build(BuildContext context) {
    node = FocusScope.of(context);
    final height = MediaQuery.of(context).size.height;
    return GestureDetector(
      onTap: () {
        FocusScope.of(context).requestFocus(FocusNode());
      },
      child: Scaffold(
          body: Container(
        height: height,
        child: Stack(
          children: <Widget>[
            Positioned(
                top: -height * .15,
                right: -MediaQuery.of(context).size.width * .4,
                child: BezierContainer()),
            SingleChildScrollView(
              child: Container(
                padding: EdgeInsets.symmetric(horizontal: 20, vertical: 18),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.center,
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: <Widget>[
                    SizedBox(height: height * 0.07),
                    _title(),
                    SizedBox(height: 20),
                    lastLogedInUser != null
                        ? CircleAvatar(
                            radius: 60,
                            foregroundImage: FileImage(
                              File(lastLogedInUser!.avatarFile),
                            ),
                          )
                        : Container(),
                    SizedBox(height: 20),
                    _usernamePasswordWidget(),
                    SizedBox(height: 20),
                    _notice(),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.end,
                      children: [
                        Checkbox(
                            value: stayLoggedIn,
                            onChanged: (bool? value) {
                              setState(() {
                                stayLoggedIn = value!;
                              });
                            }),
                        Text("Duy tr?? ????ng nh???p ?")
                      ],
                    ),
                    SizedBox(height: 20),
                    _submitButton(),
/*                  Container(
                          padding: EdgeInsets.symmetric(vertical: 10),
                          alignment: Alignment.centerRight,
                          child: Text('Qu??n m???t kh???u ?',
                              style: TextStyle(
                                  fontSize: 14, fontWeight: FontWeight.w500)),
                        ),*/
/*                  _divider(),
                        _facebookButton(),*/
                    SizedBox(height: height * .04),
                    _createAccountLabel(),
                  ],
                ),
              ),
            ),
            // Positioned(top: 40, left: 0, child: _backButton()),
          ],
        ),
      )),
    );
  }
}
