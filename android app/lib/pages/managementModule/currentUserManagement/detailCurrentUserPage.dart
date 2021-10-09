import 'dart:io';

import 'package:bkrm/pages/managementModule/currentUserManagement/changePasswordPage.dart';
import 'package:bkrm/services/services.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:flutter_datetime_picker/flutter_datetime_picker.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import 'package:page_transition/page_transition.dart';

import 'package:bkrm/pages/Nav2App.dart';
import 'package:url_launcher/url_launcher.dart';

class CurrentUserDetailPage extends StatefulWidget {
  @override
  _CurrentUserDetailPageState createState() => _CurrentUserDetailPageState();
}

class _CurrentUserDetailPageState extends State<CurrentUserDetailPage> {
  final _formKey = GlobalKey<FormState>();
  final ImagePicker picker = ImagePicker();
  String? genderValue;
  DateTime? dateOfBirth;

  bool enableEdit = false;

  bool needRefresh = false;

  TextEditingController userNameController = TextEditingController();
  TextEditingController fullNameController = TextEditingController();
  TextEditingController dateOfBirthController = TextEditingController();
  TextEditingController phoneController = TextEditingController();
  TextEditingController emailController = TextEditingController();
  TextEditingController branchNameController = TextEditingController();
  TextEditingController branchAddressController = TextEditingController();
  late File imageFile;

  bool fullNameValid = false;
  bool emailValid = true;
  bool phoneValid = false;

  bool? selling = false;
  bool? managing = false;
  bool? purchasing = false;
  bool? reporting = false;

  @override
  void initState() {
    super.initState();
    setInfoFromEmployee();
  }

  setInfoFromEmployee() {
    userNameController.text = BkrmService().currentUser!.username;
    fullNameController.text = BkrmService().currentUser!.name;
    if (BkrmService().currentUser!.dateOfBirth != null) {
      dateOfBirthController.text = DateFormat("dd-MM-yyyy")
          .format(BkrmService().currentUser!.dateOfBirth!);
    }
    dateOfBirth = BkrmService().currentUser!.dateOfBirth;

    phoneController.text = BkrmService().currentUser!.phone ?? "";
    emailController.text = BkrmService().currentUser!.email ?? "";
    genderValue = BkrmService().currentUser!.gender;
    branchNameController.text = BkrmService().currentUser!.branchName;
    branchAddressController.text = BkrmService().currentUser!.branchAddress;
    selling = BkrmService().currentUser!.roles.contains("selling");
    purchasing = BkrmService().currentUser!.roles.contains("purchasing");
    managing = BkrmService().currentUser!.roles.contains("managing");
    reporting = BkrmService().currentUser!.roles.contains("reporting");
    imageFile = File(BkrmService().currentUser!.avatarFile);
  }

  clearInfo() {
    userNameController.text = "";
    fullNameController.text = "";
    dateOfBirthController.text = "";
    dateOfBirth = null;
    phoneController.text = "";
    emailController.text = "";
    genderValue = null;
    branchAddressController.text = "";
    branchAddressController.text = "";
    selling = null;
    purchasing = null;
    managing = null;
    reporting = null;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      resizeToAvoidBottomInset: true,
      appBar: AppBar(
        title: Text("Thông tin cá nhân"),
      ),
      body: WillPopScope(
        onWillPop: () async {
          Navigator.pop(context, needRefresh);
          return needRefresh;
        },
        child: SingleChildScrollView(
          child: Container(
            padding: EdgeInsets.all(8.0),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  SizedBox(
                    height: 30,
                  ),
                  Center(
                    child: Container(
                      decoration: BoxDecoration(
                          border: Border.all(),
                          borderRadius: BorderRadius.circular(8.0)
                      ),
                        child: ClipRRect(
                          borderRadius: BorderRadius.circular(8.0),
                          child: Container(
                            height: 150,
                            width: 150,
                            child: Image.file(
                      imageFile,
                      width: 100,
                      height: 100,
                    ),
                          ),
                        )),
                  ),
                  SizedBox(height: 10,),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      IconButton(
                          icon: Icon(
                            Icons.folder,
                            color: enableEdit?Colors.blueAccent:Colors.grey,
                          ),
                          onPressed: enableEdit?() async {
                            PickedFile? image = await picker.getImage(
                                source: ImageSource.gallery,
                                maxWidth: 300,
                                maxHeight: 300);
                            setState(() {
                              imageFile = File(image!.path);
                            });
                          }:null),
                      IconButton(
                        icon: Icon(
                          Icons.camera_alt,
                          color: enableEdit?Colors.blueAccent:Colors.grey,
                        ),
                        onPressed: enableEdit?() async {
                          PickedFile? image = await picker.getImage(
                              source: ImageSource.camera,
                              maxWidth: 300,
                              maxHeight: 300);
                          setState(() {
                            imageFile = File(image!.path);
                          });
                        }:null,
                      )
                    ],
                  ),
                  SizedBox(
                    height: 20,
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "Tên đăng nhập: ",
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          enabled: false,
                          controller: userNameController,
                        ),
                      )
                    ],
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "Họ tên: ",
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          enabled: enableEdit,
                          controller: fullNameController,
                          validator: (name) {
                            if (name == "" || name == null) {
                              fullNameValid = false;
                              return " *Bắt buộc";
                            }
                            fullNameValid = true;
                            return null;
                          },
                          decoration: InputDecoration(
                              enabledBorder: const UnderlineInputBorder(
                                  borderSide: BorderSide(color: Colors.blue))),
                        ),
                      )
                    ],
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "Email: ",
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          enabled: enableEdit,
                          autovalidateMode: AutovalidateMode.always,
                          validator: (email) {
                            if (email == "" || email == null) {
                              emailValid = true;
                              return null;
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
                          },
                          controller: emailController,
                          decoration: InputDecoration(
                              enabledBorder: const UnderlineInputBorder(
                                  borderSide: BorderSide(color: Colors.blue))),
                        ),
                      )
                    ],
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "Số điện thoại: ",
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          keyboardType: TextInputType.phone,
                          enabled: enableEdit,
                          controller: phoneController,
                          autovalidateMode: AutovalidateMode.always,
                          validator: (phone) {
                            if (phone == null || phone == "") {
                              phoneValid = false;
                              return " *Bắt buộc";
                            }
                            phoneValid = true;
                            return null;
                          },
                          decoration: InputDecoration(
                              enabledBorder: const UnderlineInputBorder(
                                  borderSide: BorderSide(color: Colors.blue))),
                        ),
                      )
                    ],
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  Row(
                    children: [
                      Text(
                        "Giới tính: ",
                        style: TextStyle(
                            fontSize: 16, fontWeight: FontWeight.w500),
                      ),
                      Radio(
                          activeColor: enableEdit ? Colors.blue : Colors.grey,
                          value: "male",
                          groupValue: genderValue,
                          onChanged: (dynamic value) {
                            if (!enableEdit) {
                              return;
                            }
                            genderValue = value;
                            setState(() {});
                          }),
                      Text(
                        "Nam",
                        style: TextStyle(
                            fontWeight: FontWeight.bold, fontSize: 15),
                      ),
                      Radio(
                          activeColor: enableEdit ? Colors.blue : Colors.grey,
                          value: "female",
                          groupValue: genderValue,
                          onChanged: (dynamic value) {
                            if (!enableEdit) {
                              return;
                            }
                            genderValue = value;
                            setState(() {});
                          }),
                      Text(
                        "Nữ",
                        style: TextStyle(
                            fontWeight: FontWeight.bold, fontSize: 15),
                      ),
                    ],
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "Ngày sinh: ",
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: InkWell(
                          onTap: () {
                            if (!enableEdit) {
                              return;
                            }
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
                              enabled: enableEdit,
                              readOnly: true,
                              controller: dateOfBirthController,
                              decoration: InputDecoration(
                                  enabledBorder: const UnderlineInputBorder(
                                      borderSide:
                                          BorderSide(color: Colors.blue))),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                  SizedBox(
                    height: 20,
                  ),
                  Text(
                    "Các quyền cho phép: ",
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  Row(
                    children: [
                      Checkbox(value: selling, onChanged: null),
                      Text("Bán hàng"),
                      Checkbox(value: purchasing, onChanged: null),
                      Text("Kho hàng"),
                    ],
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  Row(
                    children: [
                      Checkbox(value: managing, onChanged: null),
                      Text("Nhân sự"),
                      Checkbox(value: reporting, onChanged: null),
                      Text("Quản lý và xem báo cáo"),
                    ],
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "Tên cửa hàng: ",
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          enabled: false,
                          controller: branchNameController,
                        ),
                      )
                    ],
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "Địa chỉ cửa hàng: ",
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          enabled: false,
                          controller: branchAddressController,
                        ),
                      )
                    ],
                  ),
                  SizedBox(
                    height: 40,
                  ),
                  Divider(),
                  Center(
                      child: !enableEdit
                          ? ElevatedButton(
                              child: Container(
                                padding: EdgeInsets.all(10.0),
                                color: Colors.blue,
                                child: Text(
                                  enableEdit ? "Xác nhận" : "Chỉnh sửa",
                                  style: TextStyle(
                                      color: Colors.white, fontSize: 20),
                                ),
                              ),
                              onPressed: () async {
                                if (!enableEdit) {
                                  enableEdit = true;
                                  setState(() {});
                                  return;
                                }
                                debugPrint(fullNameValid.toString());
                                debugPrint(emailValid.toString());
                                debugPrint(phoneValid.toString());
                                _formKey.currentState!.validate();
                                if (fullNameValid && emailValid && phoneValid) {
                                  showDialog(
                                      context: context,
                                      builder: (context) {
                                        return AlertDialog(
                                          content: Container(
                                            height: 60,
                                            child: Center(
                                              child:
                                                  CircularProgressIndicator(),
                                            ),
                                          ),
                                        );
                                      });
                                  MsgInfoCode returnCode =
                                      await BkrmService().editCurrentUser(
                                    name: fullNameController.value.text,
                                    dateOfBirth: dateOfBirth,
                                    gender:
                                        genderValue == "" ? null : genderValue,
                                    email: emailController.value.text == ""
                                        ? null
                                        : emailController.value.text,
                                    phoneNumber:
                                        phoneController.value.text == ""
                                            ? ""
                                            : phoneController.value.text,
                                        avatarFile: imageFile
                                  );
                                  Navigator.pop(context);
                                  if (returnCode == MsgInfoCode.actionSuccess) {
                                    BkrmService().currentUser!.name =
                                        fullNameController.value.text;
                                    BkrmService().currentUser!.gender =
                                        genderValue;
                                    BkrmService().currentUser!.phone =
                                        phoneController.value.text;
                                    BkrmService().currentUser!.dateOfBirth =
                                        dateOfBirth;
                                    BkrmService().currentUser!.email =
                                        emailController.value.text;
                                    enableEdit = false;
                                    showDialog(
                                        context: context,
                                        builder: (context) {
                                          return AlertDialog(
                                            title:
                                                Text("Chỉnh sửa thành công."),
                                            actions: [
                                              FlatButton(
                                                  onPressed: () {
                                                    Navigator.pop(context);
                                                    this.setState(() {});
                                                  },
                                                  child: Text("Hoàn thành"))
                                            ],
                                          );
                                        });
                                    return;
                                  } else {
                                    showDialog(
                                        context: context,
                                        builder: (context) {
                                          return AlertDialog(
                                            title: Text("Chỉnh sửa thất bại"),
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
                                }
                              },
                            )
                          : Row(
                              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                              children: [
                                  enableEdit
                                      ? ElevatedButton(
                                          onPressed: () {
                                            clearInfo();
                                            setInfoFromEmployee();
                                            enableEdit = false;
                                            setState(() {});
                                          },
                                          child: Container(
                                              padding: EdgeInsets.all(10.0),
                                              child: Text(
                                                "Hủy",
                                                style: TextStyle(fontSize: 20),
                                              )))
                                      : Container(),
                                  ElevatedButton(
                                    child: Container(
                                      padding: EdgeInsets.all(10.0),
                                      color: Colors.blue,
                                      child: Text(
                                        enableEdit ? "Xác nhận" : "Chỉnh sửa",
                                        style: TextStyle(
                                            color: Colors.white, fontSize: 20),
                                      ),
                                    ),
                                    onPressed: () async {
                                      if (!enableEdit) {
                                        enableEdit = true;
                                        setState(() {});
                                        return;
                                      }
                                      debugPrint(fullNameValid.toString());
                                      debugPrint(emailValid.toString());
                                      debugPrint(phoneValid.toString());
                                      _formKey.currentState!.validate();
                                      if (fullNameValid &&
                                          emailValid &&
                                          phoneValid) {
                                        showDialog(
                                            context: context,
                                            builder: (context) {
                                              return AlertDialog(
                                                content: Container(
                                                  height: 60,
                                                  child: Center(
                                                    child:
                                                        CircularProgressIndicator(),
                                                  ),
                                                ),
                                              );
                                            });
                                        MsgInfoCode returnCode =
                                            await BkrmService().editCurrentUser(
                                          name: fullNameController.value.text,
                                          dateOfBirth: dateOfBirth,
                                          gender: genderValue == ""
                                              ? null
                                              : genderValue,
                                          email:
                                              emailController.value.text == ""
                                                  ? null
                                                  : emailController.value.text,
                                          phoneNumber:
                                              phoneController.value.text == ""
                                                  ? ""
                                                  : phoneController.value.text,
                                              avatarFile: imageFile
                                        );
                                        Navigator.pop(context);
                                        if (returnCode ==
                                            MsgInfoCode.actionSuccess) {
                                          BkrmService().currentUser!.name =
                                              fullNameController.value.text;
                                          BkrmService().currentUser!.gender =
                                              genderValue;
                                          BkrmService().currentUser!.phone =
                                              phoneController.value.text;
                                          BkrmService()
                                              .currentUser!
                                              .dateOfBirth = dateOfBirth;
                                          BkrmService().currentUser!.email =
                                              emailController.value.text;
                                          BkrmService().currentUser!.avatarFile=imageFile.path;
                                          BkrmService().prepareDataForOffline();
                                          enableEdit = false;
                                          needRefresh=true;
                                          showDialog(
                                              context: context,
                                              builder: (context) {
                                                return AlertDialog(
                                                  title: Text(
                                                      "Chỉnh sửa thành công."),
                                                  actions: [
                                                    FlatButton(
                                                        onPressed: () {
                                                          Navigator.pop(
                                                              context);
                                                          this.setState(() {});
                                                        },
                                                        child:
                                                            Text("Hoàn thành"))
                                                  ],
                                                );
                                              });
                                          return;
                                        } else {
                                          showDialog(
                                              context: context,
                                              builder: (context) {
                                                return AlertDialog(
                                                  title: Text(
                                                      "Chỉnh sửa thất bại"),
                                                  actions: [
                                                    FlatButton(
                                                        onPressed: () {
                                                          Navigator.pop(
                                                              context);
                                                        },
                                                        child: Text("Đóng"))
                                                  ],
                                                );
                                              });
                                        }
                                      }
                                    },
                                  ),
                                ])),
                  SizedBox(
                    height: 30,
                  ),
                  Center(
                    child: ElevatedButton(
                        onPressed: () {
                          Navigator.push(context,
                              PageTransition(child: ChangePasswordPage(),type: pageTransitionType));
                        },
                        child: Container(
                            padding: EdgeInsets.all(10.0),
                            color: Colors.blue,
                            child: Text(
                              "Đổi mật khẩu",
                              style: TextStyle(fontSize: 20),
                            ))),
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  Center(child: InkWell(
                    onTap: ()async{
                      await canLaunch(
                          "https://bkrm.store/bkrm/public/privacy-policy" +
                              BkrmService()
                                  .currentUser!
                                  .branchId
                                  .toString() +
                              "/item-list")
                          ? await launch(
                          "https://bkrm.store/bkrm/public/privacy-policy" +
                              BkrmService()
                                  .currentUser!
                                  .branchId
                                  .toString() +
                              "/item-list")
                          : showDialog(
                          context: context,
                          builder: (context) {
                            return AlertDialog(
                              title: Text(
                                  "Không mở được trình duyệt để truy cập."),
                              actions: [
                                TextButton(
                                    onPressed: () {
                                      Navigator.pop(context);
                                    },
                                    child: Text("Đóng"))
                              ],
                            );
                          });
                    },
                    child: Text(
                      "Chính sách quyền riêng tư",
                      style: TextStyle(
                          decoration: TextDecoration.underline,
                          color: Colors.blue),
                    ),
                  ),),
                  SizedBox(
                    height: 40,
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
