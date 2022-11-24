---------------------- spi_bodega_inv_compras ---------------------------------	
alter PROCEDURE spi_bodega_inv_compras
AS
BEGIN
	declare @TEMPO TABLE    
	   (COD_PRODUCTO				varchar(100)
		,NOM_PRODUCTO				varchar(100)
		,CANT_STOCK					numeric(10,2)
		,CANT_SOLICITADA			numeric(10,2)
		,CANT_RECIBIDA				numeric(10,2)
		,CANT_POR_RECIBIR			numeric(10,2)
		)

	declare
		@K_BODEGA	numeric

	set @K_BODEGA = 2		-- eq terminado 
	--------------------------
	-- inserta las solicitudes
	insert into @TEMPO
	   (COD_PRODUCTO				
		,NOM_PRODUCTO				
		,CANT_STOCK					
		,CANT_SOLICITADA			
		,CANT_RECIBIDA				
		,CANT_POR_RECIBIR			
		)
	select P.COD_PRODUCTO
			,P.NOM_PRODUCTO
			,dbo.f_bodega_stock(P.COD_PRODUCTO, @K_BODEGA, getdate()) CANTIDAD
			,(select sum(s.cantidad) 
			  from solicitud_compra s 
			  where s.cod_producto = p.cod_producto
				and dbo.f_sol_por_llegar(cod_solicitud_compra) > 0
				and S.COD_ESTADO_SOLICITUD_COMPRA = 2)	-- aprobado		-- CANT_SOLICITADA
			,(select sum(dbo.f_sol_recibido(s.cod_solicitud_compra)) 
			  from solicitud_compra s 
			  where s.cod_producto = p.cod_producto
				and dbo.f_sol_por_llegar(cod_solicitud_compra) > 0
				and S.COD_ESTADO_SOLICITUD_COMPRA = 2)	-- aprobado		-- CANT_RECIBIDA
			,dbo.f_bodega_por_recibir(P.COD_PRODUCTO) POR_RECIBIR
	from PRODUCTO P left outer join MARCA M on M.COD_MARCA = P.COD_MARCA
	where substring(sistema_valido, 2, 1) = 'S'
	  and P.maneja_inventario = 'S'

	select *
	from @TEMPO 
	order by NOM_PRODUCTO
END