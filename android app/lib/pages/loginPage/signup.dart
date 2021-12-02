import 'package:bkrm/services/services.dart';
import 'package:flutter/material.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_datetime_picker/flutter_datetime_picker.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';

import 'package:bkrm/pages/Nav2App.dart';
import 'package:bkrm/pages/loginPage/Widget/bezierContainer.dart';
import 'package:bkrm/pages/loginPage/loginPage.dart';
import 'package:page_transition/page_transition.dart';

class SignUpPage extends StatefulWidget {
  SignUpPage({Key? key, this.title}) : super(key: key);

  final String? title;

  @override
  _SignUpPageState createState() => _SignUpPageState();
}

class _SignUpPageState extends State<SignUpPage> {
  BkrmService bkrmService = BkrmService();
  final _formKey = GlobalKey<FormState>();

  TextEditingController userNameController = TextEditingController();
  TextEditingController emailController = TextEditingController();
  TextEditingController phoneController = TextEditingController();
  TextEditingController passwordController = TextEditingController();
  TextEditingController confirmPasswordController = TextEditingController();
  TextEditingController fullNameController = TextEditingController();
  TextEditingController dateOfBirthController = TextEditingController();
  TextEditingController storeNameController = TextEditingController();
  TextEditingController storeAddressController = TextEditingController();

  DateTime? dateOfBirth;

  bool emailValid = false;
  bool usernameValid = false;
  bool passwordValid = false;
  bool confirmPasswordValid = false;
  bool fullNameValid = false;
  bool dateOfBirthValid = false;
  bool phoneValid = false;
  bool genderValid = false;
  bool storeNameValid = false;
  bool storeAddressValid = false;
  String? genderValue;
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
            Text('Quay lại',
                style: TextStyle(fontSize: 12, fontWeight: FontWeight.w500))
          ],
        ),
      ),
    );
  }

  Widget _entryField(String title,
      {bool isPassword = false,
      TextEditingController? controller,
      String? Function(String?)? validation,
      int? maxLength}) {
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
              maxLength: maxLength,
              autovalidateMode: AutovalidateMode.onUserInteraction,
              validator: validation != null
                  ? validation
                  : (String) {
                      return "";
                    },
              controller: controller,
              obscureText: isPassword,
              decoration: InputDecoration(
                  counterText: "",
                  border: InputBorder.none,
                  fillColor: Color(0xfff3f3f4),
                  filled: true))
        ],
      ),
    );
  }

  Widget _phoneNumber() {
    return Container(
      margin: EdgeInsets.symmetric(vertical: 10),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          Text(
            "Số điện thoại",
            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
          ),
          SizedBox(
            height: 10,
          ),
          TextFormField(
              autovalidateMode: AutovalidateMode.onUserInteraction,
              validator: (phone) {
                if (phone == "" || phone == null) {
                  phoneValid = false;
                  return " * Bắt buộc";
                }
                phoneValid = true;
                return null;
              },
              controller: phoneController,
              keyboardType: TextInputType.phone,
              decoration: InputDecoration(
                  border: InputBorder.none,
                  fillColor: Color(0xfff3f3f4),
                  filled: true))
        ],
      ),
    );
  }

  Widget _dateOfBirth() {
    return Container(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            "Ngày sinh:",
            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
          ),
          SizedBox(
            height: 10,
          ),
          InkWell(
            onTap: () {
              DatePicker.showDatePicker(context,
                  locale: LocaleType.vi,
                  currentTime: DateTime.now(),
                  maxTime: DateTime.now(), onConfirm: (date) {
                dateOfBirth = date;
                dateOfBirthController.text =
                    DateFormat("dd-MM-yyyy").format(date);
                setState(() {});
              });
            },
            child: IgnorePointer(
              child: TextFormField(
                  validator: (value) {
                    if (value == "" || value == null) {
                      dateOfBirthValid = false;
                      return " *Bắt buộc";
                    }
                    dateOfBirthValid = true;
                    return null;
                  },
                  readOnly: true,
                  controller: dateOfBirthController,
                  decoration: InputDecoration(
                      border: InputBorder.none,
                      fillColor: Color(0xfff3f3f4),
                      filled: true)),
            ),
          ),
        ],
      ),
    );
  }

  Widget genderCheckbox() {
    return Container(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            height: 10,
          ),
          Text(
            "Giới tính",
            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
          ),
          SizedBox(
            height: 10,
          ),
          Row(
            children: [
              Radio(
                  value: "male",
                  groupValue: genderValue,
                  onChanged: (dynamic value) {
                    genderValue = value;
                    genderValid = true;
                    setState(() {});
                  }),
              Text(
                "Nam",
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
              ),
              Radio(
                  value: "female",
                  groupValue: genderValue,
                  onChanged: (dynamic value) {
                    genderValue = value;
                    genderValid = true;
                    setState(() {});
                  }),
              Text(
                "Nữ",
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
              ),
            ],
          ),
          Text(
            genderValid ? "" : " * Bắt buộc",
            style: TextStyle(color: Colors.red, fontSize: 12),
          ),
        ],
      ),
    );
  }

  Widget _submitButton() {
    return InkWell(
      onTap: () async {
        _formKey.currentState!.validate();
        if (storeAddressValid &&
            storeNameValid &&
            usernameValid &&
            emailValid &&
            confirmPasswordValid &&
            phoneValid &&
            dateOfBirthValid &&
            genderValid &&
            passwordValid) {
          showDialog(
              context: context,
              builder: (context) {
                return AlertDialog(
                  content: SizedBox(
                    height: 100,
                    child: Center(
                      child: CircularProgressIndicator(),
                    ),
                  ),
                );
              });
          MsgInfoCode signUpStatus = await bkrmService
              .signUp(
                  username: userNameController.value.text,
                  email: emailController.value.text,
                  name: fullNameController.value.text,
                  password: passwordController.value.text,
                  phoneNumber: phoneController.value.text,
                  gender: genderValue,
                  dateOfBirth: dateOfBirth,
                  branchName: storeNameController.value.text,
                  branchAddress: storeAddressController.value.text)
              .then((returnMsgCode) {
            Navigator.pop(context);
            switch (returnMsgCode) {
              case MsgInfoCode.signUpFail:
                showDialog(
                    context: context,
                    builder: (context) {
                      return AlertDialog(
                        content: Text(
                          "Đăng ký không thành công",
                        ),
                        actions: [
                          FlatButton(
                              onPressed: () {
                                Navigator.pop(context);
                              },
                              child: Text("Đóng"))
                        ],
                      );
                    });
                break;
              case MsgInfoCode.signUpSuccess:
                showDialog(
                    context: context,
                    builder: (context) {
                      return AlertDialog(
                        content: Text(
                          "Đăng ký thành công.",
                        ),
                        actions: [
                          FlatButton(
                              onPressed: () {
                                Navigator.pop(context);
                                Navigator.pop(context);
                              },
                              child: Text("Hoàn thành")),
                          FlatButton(
                              onPressed: () {
                                Navigator.pop(context);
                                Navigator.pop(context);
                                Navigator.push(
                                    context,
                                    PageTransition(
                                        child: LoginPage(
                                          userName:
                                              userNameController.value.text,
                                          password:
                                              passwordController.value.text,
                                        ),
                                        type: pageTransitionType));
                              },
                              child: Text("Đăng nhập")),
                        ],
                      );
                    });
                break;
              case MsgInfoCode.usernameAlreadyBeenTaken:
                showDialog(
                    context: context,
                    builder: (context) {
                      return AlertDialog(
                        content: Container(
                          height: 50,
                          child: Center(
                            child: Text(
                              "Tên đăng nhập hoặc email đã tồn tại.",
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                  fontSize: 18, fontWeight: FontWeight.bold),
                            ),
                          ),
                        ),
                        actions: [
                          FlatButton(
                              onPressed: () {
                                Navigator.pop(context);
                              },
                              child: Text("Đóng"))
                        ],
                      );
                    });
                break;
              default:
                showDialog(
                    context: context,
                    builder: (context) {
                      return AlertDialog(
                        content: Text(
                          "Không có kết nối mạng.",
                        ),
                        actions: [
                          FlatButton(
                              onPressed: () {
                                Navigator.pop(context);
                              },
                              child: Text("Đóng"))
                        ],
                      );
                    });
            }
            return returnMsgCode;
          }).timeout(Duration(seconds: 60), onTimeout: () {
            showDialog(
                context: context,
                builder: (context) {
                  return AlertDialog(
                    content: Text(
                      "Không kết nối được với hệ thống.",
                    ),
                    actions: [
                      FlatButton(
                          onPressed: () {
                            Navigator.pop(context);
                          },
                          child: Text("Đóng"))
                    ],
                  );
                });
            return MsgInfoCode.logInFail;
          });
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
          'Đăng ký',
          style: TextStyle(fontSize: 20, color: Colors.white),
        ),
      ),
    );
  }

  Widget _loginAccountLabel() {
    return InkWell(
      onTap: () {
        Navigator.pushNamed(context, Nav2App.loginRoute);
      },
      child: Container(
        margin: EdgeInsets.symmetric(vertical: 20),
        padding: EdgeInsets.all(15),
        alignment: Alignment.bottomCenter,
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: <Widget>[
            Text(
              'Đã có tài khoản ?',
              style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
            ),
            SizedBox(
              width: 10,
            ),
            Text(
              'Đăng nhập',
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
            fontSize: 30,
            fontWeight: FontWeight.w700,
            color: Color(0xff1565c0),
          ),
          children: [
            TextSpan(
              text: 'K',
              style: TextStyle(color: Colors.black, fontSize: 30),
            ),
            TextSpan(
              text: 'RM',
              style: TextStyle(color: Color(0xff1565c0), fontSize: 30),
            ),
          ]),
    );
  }

  Widget _emailPasswordWidget() {
    return Form(
      key: _formKey,
      autovalidateMode: AutovalidateMode.onUserInteraction,
      child: Column(
        children: <Widget>[
          _entryField("Tên đăng nhập",
              controller: userNameController,
              maxLength: 20, validation: (String? username) {
            if (username == null || username == "") {
              usernameValid = false;
              return "* Bắt buộc";
            }
            usernameValid = true;
            return null;
          }),
          _entryField("Email", controller: emailController,
              validation: (email) {
            if (email == null || email == "") {
              emailValid = false;
              return "* Băt buộc";
            }
            if (RegExp(
                    r"^[a-zA-Z0-9.a-zA-Z0-9.!#$%&'*+-/=?^_`{|}~]+@[a-zA-Z0-9]+\.[a-zA-Z]+")
                .hasMatch(email)) {
              emailValid = true;
              return null;
            } else {
              emailValid = false;
              return "Email không hợp lệ";
            }
          }),
          _entryField("Mật khẩu",
              isPassword: true,
              controller: passwordController, validation: (password) {
            if (password == null || password == "") {
              passwordValid = false;
              return " * Băt buộc";
            } else {
              passwordValid = true;
              return null;
            }
          }),
          _entryField("Nhập lại mật khẩu",
              isPassword: true, controller: confirmPasswordController,
              validation: (confirmPassword) {
            if (confirmPassword == null || confirmPassword == "") {
              confirmPasswordValid = false;
              return " * Băt buộc";
            } else {
              if (confirmPasswordController.value.text !=
                  passwordController.value.text) {
                confirmPasswordValid = false;
                return "* Không giống với mật khẩu";
              }
              confirmPasswordValid = true;
              return null;
            }
          }),
          _phoneNumber(),
          _entryField("Họ tên", controller: fullNameController,
              validation: (String? fullName) {
            if (fullName == null || fullName == "") {
              fullNameValid = false;
              return "* Bắt buộc";
            }
            fullNameValid = true;
            return null;
          }),
          _dateOfBirth(),
          genderCheckbox(),
          _entryField("Tên cửa hàng", controller: storeNameController,
              validation: (storeName) {
            if (storeName == null || storeName == "") {
              storeNameValid = false;
              return "* Băt buộc";
            } else {
              storeNameValid = true;
              return null;
            }
          }),
          _entryField("Địa chỉ cửa hàng", controller: storeAddressController,
              validation: (storeAddress) {
            if (storeAddress == null || storeAddress == "") {
              storeAddressValid = false;
              return " * Băt buộc";
            } else {
              storeAddressValid = true;
              return null;
            }
          }),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
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
                top: -MediaQuery.of(context).size.height * .15,
                right: -MediaQuery.of(context).size.width * .4,
                child: BezierContainer(),
              ),
              Container(
                padding: EdgeInsets.symmetric(horizontal: 20),
                child: SingleChildScrollView(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.center,
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: <Widget>[
                      SizedBox(height: height * .2),
                      _title(),
                      SizedBox(
                        height: 50,
                      ),
                      _emailPasswordWidget(),
                      SizedBox(
                        height: 20,
                      ),
                      _submitButton(),
                      SizedBox(height: height * .14),
                      _loginAccountLabel(),
                    ],
                  ),
                ),
              ),
              Positioned(top: 40, left: 0, child: _backButton()),
            ],
          ),
        ),
      ),
    );
  }
}
