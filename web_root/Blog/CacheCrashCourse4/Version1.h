struct Version1
{
	//"32bit booleans are faster"
	typedef uint32_t Bool;

	//"Let's encapsulate the transform for reuse later!"
	struct TransformData
	{
		float ActualData1[8];
	};

	struct MyData
	{
		MyData()
		{
			Transform = make_shared<TransformData>();
		}

		//"To save memory between objects sharing the same transform"
		shared_ptr<TransformData> Transform;

		Bool Data1;
		int Data2;
		char Data3;
		Bool Data4;
		float Data5;
		Bool Data6;
		void* Data7;
		char OtherStuff[483];
		Bool Data8;
		string Data9;
		float Data10;
		Bool Data11;
		int Data12;
		vector<char> Data13;
		Bool Data14;
		float ActualData2[8];
		Bool MoreFlags[17];
		float Result = 0.0f;
	};

	float* GetActualData1(MyData& item, int index)
	{
		return item.Transform->ActualData1;
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

	list<MyData> Data;
};