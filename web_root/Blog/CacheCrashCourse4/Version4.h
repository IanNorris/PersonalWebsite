struct Version4
{
	//"32bit booleans are faster"
	typedef uint32_t Bool;

	struct MyData
	{
		vector<char> Data13;
		string Data9;
		void* Data7;
		float Data5;
		float Data10;
		float ActualData1[8];
		float ActualData2[8];
		float Result = 0.0f;
		int Data2;
		int Data12;
		char Data3;
		Bool Data1;
		Bool Data4;
		Bool Data6;
		Bool Data8;
		Bool Data11;
		Bool Data14;
		Bool MoreFlags[17];
		char OtherStuff[483];
	};

	float* GetActualData1(MyData& item, int index)
	{
		return item.ActualData1;
	}

	float* GetActualData2(MyData& item, int index)
	{
		return item.ActualData2;
	}

	void SetupExtraData(MyData& item, int index)
	{
		//This simulates memory fragmentation
		item.Data13.resize(index % 679);
		item.Data9 = "hello";
	}

	void WriteResult(MyData& item, float result, int index)
	{
		item.Result = result;
	}

	float GetResult(const MyData& item, int index) const
	{
		return item.Result;
	}

	vector<MyData> Data;
};