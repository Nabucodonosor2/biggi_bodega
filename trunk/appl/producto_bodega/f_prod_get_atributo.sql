------------------f_prod_get_atributo----------------
--	ESTA FUNCION DESPLIEGA LA FECHA DE EMISION EN LOS REPORTES EN DISTINTO FORMATO--
CREATE function f_prod_get_atributo(@ve_cod_producto varchar (30))
RETURNS varchar(8000) --  debe retornar un text, pero no permite
AS
BEGIN
	declare @res varchar(8000),  --  debe ser un text
			@nom_atributo_producto varchar(1000)

	declare c_cursor cursor for 
	select nom_atributo_producto from atributo_producto
	where cod_producto = @ve_cod_producto

	open c_cursor 
	fetch c_cursor into @nom_atributo_producto

	set @res = ''
	while @@fetch_status = 0 
	begin
		set @res = @res + '- ' + @nom_atributo_producto + Char(10)
		fetch c_cursor into @nom_atributo_producto
	end
	close c_cursor
	deallocate c_cursor

	if (@res<>'')
		set @res = substring(@res, 1, len(@res)-1)
	return @res;
END
go